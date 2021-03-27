<?php

namespace App\Forms;

use App\Models\UserModel;
use Nette\Utils\Validators;

use Nette\Security\UserStorage;

class SettingsForm
{
    const
    FORM_MSG_REQUIRED = 'this field is required';

    public CustomFormFactory $forms;
    public UserModel $user;

    public function __construct(UserModel $user, CustomFormFactory $forms)
    {
        $this->user = $user;
        $this->forms = $forms;
    }

    public function renderEmailForm()
    {
        $form = $this->forms->create();

        $form->addHidden('action', 'email');
        $form->addText('email', 'Your current email:')
            ->setDefaultValue($this->user->getEmailValue()) //$this->user->testUser->getIdentity()->email  ??
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('readonly');
        $form->addText('newemail', 'New email:')
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->addRule($form::EMAIL)
            //check if valid email
            ->addRule(
                function ($item) {
                    if (!Validators::isEmail($item->value)) {
                        return false;
                    }
                    return true;
                }, "This email is not valid"
            )
             // check unique record in form
            ->addRule(
                function ($item) {
                    if ($this->user->getValue($item->value)) {
                        return false;
                    }
                    return true;
                }, "This email already registered"
            )
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('reemail', 'Confirm email:')
            ->addRule($form::EQUAL, 'Email mismatch', $form['newemail'])
            ->setHtmlAttribute('class', 'form-control')
            ->setOmitted();
        $form->addPassword('password', 'Confirm change with your password:')
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->setHtmlAttribute('class', 'form-control');
        $form->addSubmit('submit', 'Update Email')
            ->setHtmlAttribute('class', 'btn btn-primary');
        return $form;
    }

    public function renderPasswordForm()
    {
        $form = $this->forms->create();

        $form->addHidden('action', 'password');
        $form->addHidden('email', $this->user->testUser->getIdentity()->email);
        
        $form->addPassword('password', 'Current Password:')
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->setHtmlAttribute('class', 'form-control');
        $form->addPassword('newpassword', 'New Password:')
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->addRule($form::MIN_LENGTH, 'Password has to be minimum of %d letters', 4)
            ->addRule($form::MAX_LENGTH, 'Password has to be maximum of %d letters', 40)
            ->setHtmlAttribute('class', 'form-control');
        $form->addPassword('repassword', 'Confirm Password:')
            ->addRule($form::EQUAL, 'Password mismatch', $form['newpassword'])
            ->setHtmlAttribute('class', 'form-control')
            ->setOmitted();
        $form->addSubmit('submit', 'Update Password')
            ->setHtmlAttribute('class', 'btn btn-primary');
        return $form;
    }

    public function renderAvatarForm()
    {
        $form = $this->forms->create();

        $form->addUpload('avatar', 'From Your File:')
            ->addRule($form::IMAGE, 'Avatar musí být JPEG, PNG, GIF or WebP.')
            ->addRule($form::MAX_FILE_SIZE, 'Maximální velikost je 1 MB.', 1024 * 1024)
            ->setHtmlAttribute('onchange', 'previewFile(this)');
        $form->addSubmit('submit', 'Upload Avatar')
            ->setHtmlAttribute('class', 'btn btn-primary');

        return $form;
    }

}