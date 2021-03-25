<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\UserModel;
use Nette\Utils\ArrayHash;
use App\Forms\RegisterFactory;
use Nette\Security\Passwords;
use Nette\Application\UI\Presenter;

final class RegisterPresenter extends Presenter
{
    public Passwords $passwords;
    public UserModel $users;
    public RegisterFactory $forms;

    public function __construct(
        UserModel $users, 
        Passwords $passwords, 
        RegisterFactory $forms
    )
    {
        $this->users = $users;
        $this->passwords = $passwords;
        $this->forms = $forms;
    }

    public function renderDefault()
    {
        // Předání výsledku do šablony       
    }

    protected function createComponentRegisterForm()
    {
        $form = $this->forms->renderForm();
        $form->onSuccess[] = [$this, 'RegisterFormSuccessed'];
        return $form;
    }

    public function RegisterFormSuccessed(ArrayHash $values)
    {
        //hash the password
        $values->password = $this->passwords->hash($values->password);

        $userId = $this->users->registerUser($values);
        if ($userId) {
            $this->presenter->flashMessage('You are registered', 'success');
            $this->presenter->redirect('Login:default');
        }
            $this->presenter->flashMessage('Couldn\' connect to the database.', 'fail');
            $this->presenter->redirect('Register:default');
    }
}
