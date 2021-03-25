<?php

use Nette\Security\SimpleIdentity;

class TestAuth implements Nette\Security\Authenticator
{
    private $database;
    private $passwords;

    public function __construct(
        Nette\Database\Explorer $database,
        Nette\Security\Passwords $passwords
    ) {
        $this->database = $database;
        $this->passwords = $passwords;
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {   
        $row = $this->database->table('users')
            //->where('password', $password)
            ->where('firstname', $username)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('User not found.');
        }

        if (!$this->passwords->verify($password, $row->passwords)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        return new SimpleIdentity(
            $row->id,
            $row->firstname
        );
    }
}