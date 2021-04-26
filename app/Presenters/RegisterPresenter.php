<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Http\Request;
use App\Models\UserModel;
use Nette\Utils\ArrayHash;
use App\Forms\RegisterFactory;
use App\Models\StatisticModel;
use Nette\Application\UI\Presenter;

final class RegisterPresenter extends Presenter
{
    public UserModel $user;
    public RegisterFactory $forms;
    public StatisticModel $statistic;
    public Request $request;

    public function __construct(
        UserModel $user,  
        RegisterFactory $forms,
        StatisticModel $statistic,
        Request $request
    ) {
        $this->user = $user;
        $this->forms = $forms;
        $this->statistic = $statistic;
        $this->request = $request;
    }

    public function beforeRender()
    {
        $this->statistic->saveStatistic();
        $this->template->title = 'register';
    }

    public function renderDefault()
    {   
        if ($this->request->getQuery('c')) {
            $link = $this->user->checkEmailConfirm($this->request->getQuery('c'));
            if ($link[0]) {
                $this->presenter->flashMessage($link[1], 'success');
                $this->presenter->redirect('Login:default');
            } else {
                $this->presenter->flashMessage($link[1], 'fail');
            }
        }     
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
        $values->password = $this->user->passwords->hash($values->password);

        $userId = $this->user->registerUser($values);
        if ($userId) {            
            $this->presenter->flashMessage('We have sended you confirmation email, the link will expire in 1 hour', 'success');
            $this->presenter->redirect('Login:default');
        }
            $this->presenter->flashMessage('Couldn\' connect to the database.', 'fail');
            $this->presenter->redirect('Register:default');
    }
}
