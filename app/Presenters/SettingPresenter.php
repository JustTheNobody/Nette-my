<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\UserModel;
use Nette\Utils\ArrayHash;
use App\Forms\SettingsForm;
use App\Models\FileModel;
use Nette\Security\Passwords;
use Nette\Application\UI\Presenter;

final class SettingPresenter extends Presenter //implements Authorizator
{
    public Passwords $passwords;
    public UserModel $users;
    public SettingsForm $sform;

    public $value = [];
  
    public function __construct(
        UserModel $users, 
        Passwords $passwords,
        SettingsForm $sform
    ) {
        $this->users = $users;
        $this->passwords = $passwords;
        $this->sform = $sform;
    }

    public function renderDefault(array $value)
    {
        if (isset($value['actions'])) {
            if ($value['actions'] == "avatar") {
                $avatars = new FileModel;
                $this->template->avatars = $avatars->getAvatars();
                if ($value['file'] != "") {
                    //save the avatar to db
                    $this->users->settingAvatarChange($value['file']);
                    $this->template->value = 'avatar';
                    $this->flashMessage('New Avatar has been set', 'success');
                    return;
                } 
            }
            if ($value['actions'] == "delete") {
                ($this->users->deleteUser() != 1)
                    ?
                    $this->flashMessage('Something went wrong', 'fail')//not deleted
                    :
                    $this->redirect('Login:out');//deleted
            }
            // Předání výsledku do šablony
            $this->template->value = $value['actions'];
        }        
    }

    protected function createComponentEmailForm()
    {
        $form = $this->sform->renderEmailForm();
        $form->onSuccess[] = [$this, 'formSucces'];
        return $form;
    }

    protected function createComponentPasswordForm()
    {
        $form = $this->sform->renderPasswordForm();
        $form->onSuccess[] = [$this, 'formSucces'];
        return $form;
    }

    public function formSucces(ArrayHash $values)
    {   
        $result = $this->users->autenticate($values->email, $values->password);
        
        if (is_array($result) && in_array('fail', $result)) {
            $this->flashMessage('Invalid password', 'fail');
            //redirect to email setting?
            $this->redirect('Setting:default');
        }        
        //change the email/password now
        $row = $this->users->settingChange($values);
        
        ($row != 1)? 
        $this->flashMessage("Your $values->action has not been changed.", 'fail') : 
        $this->flashMessage("Your $values->action has been changed.", 'success');

        $this->redirect('Setting:default');  
    }

    protected function createComponentAvatarForm()
    {
        $form = $this->sform->renderAvatarForm();
        $form->onSuccess[] = [$this, 'avatarFormSucces'];
        return $form;
    }

    public function avatarFormSucces(ArrayHash $values)
    {
        $row = $this->users->settingAvatarChange($values);
       
        ($row != 1)? 
        $this->flashMessage("Your avatar has not been changed.", 'fail') : 
        $this->flashMessage("Your avatar has been changed.", 'success');

        $this->redirect('Setting:default');  
    }

}
