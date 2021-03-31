<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\UserModel;
use Nette\Utils\ArrayHash;
use App\Forms\LoginFactory;
use Nette\Security\UserStorage;
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
        $this->template->title = 'login';
    }

    protected function createComponentLoginForm()
    {
        $form = $this->forms->renderForm();
        $form->onSuccess[] = [$this, 'loginFormSucces'];
        return $form;
    }

    public function loginFormSucces(ArrayHash $values)
    {
        $result = $this->user->autenticate($values->email, $values->password);

        if (is_array($result) && in_array('fail', $result)) {
            $this->flashMessage('Invalid '. $result['error'], 'fail');
            $this->redirect('Login:default');
        }

        $this->flashMessage('You are loged in.', 'success');
        $this->redirect('Homepage:default');
    }

    public function actionOut()
    {
        $this->user->testUser->getStorage()->clearAuthentication(true);
        $this->flashMessage('You have been loged out', 'success');
        $this->redirect('Homepage:default');
    }
}

