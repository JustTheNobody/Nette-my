<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Forms\AdminForm;
use App\Models\FileModel;
use App\Models\UserModel;
use App\Models\AdminModel;
use Nette\Utils\ArrayHash;
use App\Forms\SettingsForm;
use Nette\Security\Passwords;
use Nette\Application\UI\Presenter;

final class SettingPresenter extends Presenter //implements Authorizator
{
    public Passwords $passwords;
    public UserModel $users;
    public SettingsForm $sform;
    public AdminForm $aform;
    private AdminModel $admin;

    public $value = [];
  
    public function __construct(
        UserModel $users, 
        Passwords $passwords,
        SettingsForm $sform,
        AdminForm $aform,
        AdminModel $admin
    ) {
        $this->users = $users;
        $this->passwords = $passwords;
        $this->sform = $sform;
        $this->aform = $aform;
        $this->admin = $admin;

    }

    public function beforeRender()
    {
        if (!$this->users->checkAuth()) {
            $this->flashMessage('Sorry, it look like you are not loged in.', 'alert');
            $this->redirect('Login:default');
            exit;
        }
        $this->template->title = 'setting';
        $this->template->role = $this->getUser()->getIdentity()->roles;
    }

    public function renderDefault(array $value)
    {
        if (isset($value['actions'])) {
            if ($value['actions'] == "avatar") {
                $avatars = new FileModel($this->users);
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
                    $this->flashMessage('We are sorry to see you leaving', 'success');
                    $this->redirect('Login:out');//deleted
            }
            // Předání výsledku do šablony
            $this->template->value = $value['actions'];
        } 
        if (isset($value['add'])) {
            if ($value['add'] == "web") {
                $this->template->category = "web";
            }
            if ($value['add'] == "graphic") {
                $this->template->category = "graphic";
            }
            if ($value['add'] == "photography") {
                $this->template->category = "web";
            }
            $this->template->value = $value['add'];
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
            //$link = $this->link('Setting:default', ['actions' =>$values['actions']]);
           //$this->redirect($link);
           $this->redirect('Setting:default');
        }        
        //change the email/password now
        $row = $this->users->settingChange($values);     
        ($row != 1)? 
        $this->flashMessage("Your $values->actions has not been changed.", 'fail') : 
        $this->flashMessage("Your $values->actions has been changed.", 'success');

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

    /**
     * admin
     */
       
    protected function createComponentWebForm()
    {        
        $form = $this->aform->renderForm();
        $form->form['category']->setDefaultValue('web');            
        $form->onSuccess[] = [$this, 'formAdminSucces'];
        return $form;
    }

    protected function createComponentGraphicForm()
    {        
        $form = $this->aform->renderForm();
        $form->form['category']->setDefaultValue('graphic');            
        $form->onSuccess[] = [$this, 'formAdminSucces'];
        return $form;
    }

    protected function createComponentPhotographyForm()
    {        
        $form = $this->aform->renderForm();
        $form->form['category']->setDefaultValue('Photography');            
        $form->onSuccess[] = [$this, 'formAdminSucces'];
        return $form;
    }

    public function formAdminSucces(ArrayHash $values)
    {   
        
        $row = $this->admin->saveAdd($values);

        ($row && is_int($row))? 
        $this->flashMessage("Your $values->category has not been added!", 'fail') : 
        $this->flashMessage("Your $values->category has been added.", 'success');

        $this->redirect('Setting:default');  
    }

}
