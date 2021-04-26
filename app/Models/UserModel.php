<?php

namespace App\Models;

use stdClass;
use Nette\Http\Request;
use Nette\Security\User;
/**
 * Commented out at lines 31 & 42, for the user frendly output msg
 */ 
use App\Models\EmailModel;
use Nette\Utils\FileSystem;
use Nette\Database\Explorer;

use Nette\Security\Passwords;
use Nette\Security\UserStorage;
use Nette\Security\SimpleIdentity;
use Nette\Security\AuthenticationException;

class UserModel
{
    const
    DEFAULT_AVATAR_DIR = IMG_DIR . '/avatar',
    FILE_DIR = WWW_DIR . '/storage';

    public Explorer $database;
    public Passwords $passwords;
    public User $testUser;
    public Request $request;
    public EmailModel $emailModel;

    public function __construct(
        Explorer $database,
        Passwords $passwords,
        User $testUser,
        Request $request,
        EmailModel $emailModel
    ) {
        $this->database = $database;
        $this->passwords = $passwords;
        $this->testUser = $testUser;
        $this->request = $request;
        $this->emailModel = $emailModel;
    }

    public function autenticate(string $email, string $password)
    {
        $row = $this->database->table('users')
            ->where('email LIKE ?', $email)
            ->fetch();

        if (!$row) {
            //throw new AuthenticationException('User not found.');
            //just for user fendly output, I'm sure that is the best practice
            
            return ['status' => 'fail', 'error' => 'email'];
        }

        if (!$this->passwords->verify($password, $row->passwords)) {
            //throw new AuthenticationException('Invalid password.');
            
            //if bad password => record it by user_id
            
            return ['status' => 'fail', 'error' => 'password'];
        }


        $this->testUser->getStorage()->saveAuthentication(
            new SimpleIdentity(
                $row->id,
                ['role' => $row->role],
                ['name' => $row->firstname,
                 'email' => $email,
                 'avatar' => $row->avatar]
            )
        );
        
        return $this->testUser->getId();
    }

    public function checkAuth()
    {
        //check if user loged
        if ($this->testUser->getIdentity() == null) {
            return false;
        }
        return true;
    }

    public function registerUser($values)
    {
        $this->database->query(
            'INSERT INTO users ?', [
            'email' => $values->email,
            'firstname' => $values->f_name,
            'lastname' => $values->l_name,
            'passwords' => $values->password]
        );
        $userId = $this->database->getInsertId();
        //send the confirmation link now (db-> email_confirm)
        $this->sendEmailConfirm($userId, $values->email);

        // return auto-increment of the inserted row
        return $userId;
    }

    public function sendEmailConfirm($userId, $email)
    {
        //1. generate the unique $link 4 user           
        //length 32 char -> 28 77efc b5a65 6711f f9f83 33962 74514               
        $partOne = bin2hex(random_bytes(16));
        //length 16 char -> 8 77efc b5a65 6711s
        $partTwo = bin2hex(random_bytes(8));
        //count how many char is the $userId
        $userIdCount = strlen("$userId");
        $linkPart = $userIdCount . $partOne . $userId . $partTwo;
        $link = $_SERVER['HTTP_HOST'] ."/register?c=". $linkPart;
        //2. save link to emal_confirm TB
        $this->database->fetchField('SELECT link, created_at FROM email_confirm WHERE id = ?', $userId);
        $this->database->query(
            'INSERT INTO email_confirm ?', [
            'user_id' => $userId,
            'link' => $linkPart]
        );
        //send to user email address 
        $this->emailModel->sendConfirmationLink($link, $email);
        //TODO
    }

    /**
     * return array [true/false, message]
     */
    public function checkEmailConfirm($link)
    {
        //1. decode the link and compare with column link in email_confirm table
        //a. split first letter = number of userId char
        //b. than 32 char
        //c. than $1. = $userId
        $idLength = substr($link, 0, 1);
        $userId = substr($link, 33, $idLength);
        //get the data from DB
        $data = $this->database->fetchAll('SELECT link, created_at FROM email_confirm WHERE user_id = ?', $userId);

        if (empty($data)) {
            //no satch a link =>attack?
            return ['false', 'Bad link'];
        } else {
            //2. check the date/time -> 1 hour 
            $dataD = (array)$data[0]['created_at'];    
            //3600s = 1h
            $dateTime = strtotime($dataD['date']) + 3600;
    
            //3. check if the user already confirmed email (that could be sign of attack), if not change role from guest to user
            if ($data) {
                //user found
                if (strtotime('now') > $dateTime) {
                    //to late -> return array [false, 'link expired'] => delete the link from  DB
                    $this->user->database->query(
                        'DELETE FROM email_confirm WHERE ?', [
                        'user_id' => $userId]
                    );
                    return ['false', 'Link expired, you can request new link in Setting section'];
                } else {                
                    //compare time if ok compare link if ok return array [true, 'link ok']
                    if ($link != $data[0]['link']) {
                        //link not match -> attack?
                        return ['false', 'Bad link'];
                    } else {
                        //check if the user already has role = user => if yes = attack?
                        $check = ($this->database->fetchField('SELECT role FROM users WHERE id = ?', $userId) == 'guest')? true:false;
                        if ($check) {
                            //change gest to user
                            $this->database->table('users')
                                ->where('id', $userId)
                                ->update(['role'=>'user']);  
                            //delete the link from email_confirm
                            $this->database->query(
                                'DELETE FROM email_confirm WHERE ?', [
                                'user_id' => $userId]
                            );
                                return ['true', 'Email confirmed'];
                        } else {
                            //user already confirmed or no user? -> attack?
                            return ['false', 'User not found?'];
                        }                    
                    }
                }
            } else {
                //user not found -> attack?
                return ['false', 'User not found'];
            }
        }         
    }

    public function settingChange($values)
    {
        return $this->database->table('users')
            ->where('id', $this->testUser->getIdentity()->getId())
            ->update(
                ($values['actions'] == 'email')?
                ['email' => $values['newemail']]:
                ['passwords' => $this->passwords->hash($values['newpassword'])]
            );  
    }

    public function settingAvatarChange($values)
    {          
        $file = new FileModel($this);     
        if (\is_object($values) && $values->hasFile()) {
            
            $avatarValues = $file->upload($values, 'avatar');
            return $this->saveAvatarToDb($avatarValues);
            //return ($result)? true : false;
        } else {
            //copy def file avatar to user dir?
            $file->copyDefaultAvatar($values);
            return $this->saveAvatarToDb($values);
        }

    }
    
    public function saveAvatarToDb($avatar)
    {
        $result = $this->database->table('users')
            ->where('id', $this->testUser->getIdentity()->getID())
            ->update(['avatar' => $avatar]);
            //set new avatar in session
            
        $this->testUser->getIdentity()->avatar = $avatar;
        return $result;
    }

    //check the unique email
    public function getValue($email)
    {
        return $this->database->fetchField('SELECT email FROM users WHERE email = ?', $email);
    }

    //value for the 
    public function getEmailValue()
    {
        return $this->database->fetchField('SELECT email FROM users WHERE id = ?', $this->testUser->getIdentity()->getId());
    }

    public function deleteUser()
    {
        FileSystem::delete(
            "storage/" . $this->testUser->getIdentity()->getId()
        );

        $result = $this->database->query('DELETE FROM users WHERE id = ?', $this->testUser->getIdentity()->getId())
            ->getRowCount();
        return $result;
    }

    public function getEmails()
    {
        $emails = $this->database->fetchAll('SELECT * FROM email');
        foreach ($emails as &$key) {
            $key->created_at = $key->created_at->format('d.m.Y');            
        }
        return (array)$emails;
    }
}