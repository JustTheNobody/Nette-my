<?php

namespace App\Models;

use Nette\Database\Explorer;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;
use Nette\Security\Authenticator;
/**
 * Commented out at lines 31 & 42, for the user frendly output msg
 */ 
use Nette\Security\AuthenticationException;

class UserModel implements Authenticator
{
    public $database;
    private $passwords;
    public $sessionStorage;

    public function __construct(Explorer $database, Passwords $passwords) 
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function authenticate(string $email, string $password): SimpleIdentity
    {   
        $row = $this->database->table('users')
            ->where('email LIKE ?', $email)
            ->fetch();

        if (!$row) {
            //throw new AuthenticationException('User not found.');
            //just for user fendly output, I'm sure that is the best practice
            $autStatus = 'fail';
            $autError = 'user';
            return new SimpleIdentity(
                $autStatus,
                $autError   
            );
        }

        if (!$this->passwords->verify($password, $row->passwords)) {
            //throw new AuthenticationException('Invalid password.');
            //just for user fendly output, I'm sure that is the best practice
            $autStatus = 'fail';
            $autError = 'password';
            return new SimpleIdentity(
                $autStatus,
                $autError   
            );
        }

        return new SimpleIdentity(
            $row->id,
            ['role' => 'user'],
            ['name' => $row->firstname, 'email' => $email, 'avatar' => $row->avatar]
        );
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
        $this->database->table('users')
            ->where('id', $_SESSION['user_id'])
            ->update(
                ($values->action == 'email')?
                ['email' => $values->newemail]:
                ['passwords' => $values->newpassword]
            );
    }

    public function settingAvatarChange($values)
    {
        $this->database->table('users')
            ->where('id', $_SESSION['user_id'])
            ->update(['avatar' => $values]);
        //set new avatar in session
        $_SESSION['user_avatar'] = $values;
        /**
         * Upload->uploadModel(if upload avatar create user folder(id) 
         * if user deleted=> delete the userFolder to), 
         * if avatar selected from default -> no user folder,  
         * and save
         */ 
    }
    
    public function getValue($email)
    {
        return $this->database->fetchField('SELECT email FROM users WHERE email = ?', $email);
    }

    public function getEmailValue($id)
    {
        return $this->database->fetchField('SELECT email FROM users WHERE id = ?', $id);
    }

    public function deleteUser($id)
    {
        $result = $this->database->query('DELETE FROM users WHERE id = ?', $id)
                                ->getRowCount();
        return $result;
    }
}