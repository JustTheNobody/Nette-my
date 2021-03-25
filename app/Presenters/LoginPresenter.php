<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\UserModel;
use Nette\Utils\ArrayHash;
use App\Forms\LoginFactory;
use Nette\Application\UI\Presenter;

final class LoginPresenter extends Presenter //implements Authorizator
{

    public UserModel $user;
    public LoginFactory $forms;
    public $userName;

    public function __construct(UserModel $user, LoginFactory $forms)
    {
        $this->user = $user;
        $this->forms = $forms;
    }

    public function renderDefault()
    {
        // Předání výsledku do šablony
        //$this->template->userName = $this->userName;
    }

    protected function createComponentLoginForm()
    {
        $form = $this->forms->renderForm();
        $form->onSuccess[] = [$this, 'loginFormSucces'];
        return $form;
    }

    public function loginFormSucces(ArrayHash $values)
    {
        $result = $this->user->authenticate($values->email, $values->password);
        
        if ($result->id == 'fail' ) {
            $this->flashMessage('Invalid '. $result->roles[0], 'fail');
            $this->redirect('Login:default');
        }
        !isset($_SESSION)? \session_start() : '';
        $_SESSION['user'] = $result->data['name'];
        $_SESSION['user_id'] = $result->id;
        $_SESSION['user_email'] = $result->email;
        $_SESSION['user_avatar'] = $result->avatar;

        $this->flashMessage('You are loged in.', 'success');
        $this->redirect('Homepage:default');
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        session_destroy();
        $this->flashMessage('You have been loged out', 'success');
        $this->redirect('Homepage:default');
    }
}

