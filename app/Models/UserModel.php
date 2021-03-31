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
                ['role' => 'user'],
                [
                    'name' => $row->firstname,
                    'email' => $email,
                    'avatar' => $row->avatar
                ]
            )
        );
        
        return $this->testUser->getId();
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
        if (\is_object($values)) {
            $file = new FileModel;
            $result = $file->uploadAvatar($values);
            if ($result) {
                return true; 
            } else { 
                return false;
            }
            return ($result)? true : false;
        } else {
            $this->database->table('users')
                ->where('id', $this->testUser->getIdentity()->getID())
                ->update(['avatar' => $values]);
            //set new avatar in session
            $this->testUser->getIdentity()->avatar = $values;
        }
        /**
         * Upload->uploadModel(if upload avatar create user folder(id) 
         * if user deleted=> delete the userFolder to), 
         * if avatar selected from default -> no user folder,  
         * and save
         */ 
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