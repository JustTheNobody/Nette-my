<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\UserModel;
use Nette\Utils\ArrayHash;
use App\Forms\RegisterFactory;
use App\Models\StatisticModel;
use Nette\Application\UI\Presenter;

final class RegisterPresenter extends Presenter
{
    public UserModel $users;
    public RegisterFactory $forms;
    public StatisticModel $statistic;

    public function __construct(
        UserModel $users,  
        RegisterFactory $forms,
        StatisticModel $statistic
    ) {
        $this->users = $users;
        $this->forms = $forms;
        $this->statistic = $statistic;
    }

    public function beforeRender()
    {
        $this->statistic->saveStatistic();
        $this->template->title = 'register';
    }

    public function renderDefault()
    {         
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
        $values->password = $this->users->passwords->hash($values->password);

        $userId = $this->users->registerUser($values);
        if ($userId) {
            $this->presenter->flashMessage('You are registered', 'success');
            $this->presenter->redirect('Login:default');
        }
            $this->presenter->flashMessage('Couldn\' connect to the database.', 'fail');
            $this->presenter->redirect('Register:default');
    }
}
