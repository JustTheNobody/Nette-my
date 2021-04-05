<?php

namespace App\Models;

use stdClass;
use Nette\Utils\FileSystem;
use Nette\Database\Explorer;
/**
 * Commented out at lines 31 & 42, for the user frendly output msg
 */ 
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

use Nette\Security\UserStorage;
use Nette\Security\User;
class UserModel
{
    const
    DEFAULT_AVATAR_DIR = IMG_DIR . '/avatar',
    FILE_DIR = WWW_DIR . '/storage';

    public Explorer $database;
    private Passwords $passwords;
    public User $testUser;

    public function __construct(Explorer $database, Passwords $passwords, User $testUser) 
    {
        $this->database = $database;
        $this->passwords = $passwords;
        $this->testUser = $testUser;
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
            //just for user fendly output, I'm sure that is the best practice
            return ['status' => 'fail', 'error' => 'password'];
        }


        $this->testUser->getStorage()->saveAuthentication(
            new SimpleIdentity(
                $row->id,
                ['role' => $row->role],
                [
                    'name' => $row->firstname,
                    'email' => $email,
                    'avatar' => $row->avatar
                ]
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
            // return auto-increment of the inserted row
            return $this->database->getInsertId();
    }

    public function settingChange($values)
    {
        return $this->database->table('users')
            ->where('id', $this->testUser->getIdentity()->getId())
            ->update(
                ($values->actions == 'email')?
                ['email' => $values->newemail]:
                ['passwords' => $this->passwords->hash($values->newpassword)]
            );  
    }

    public function settingAvatarChange($values)
    {          
        $file = new FileModel($this);     
        if (\is_object($values) && $values->avatar->hasFile()) {
            
            $avatarValues = $file->upload($values->avatar, 'avatar');
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
}