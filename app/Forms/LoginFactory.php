<?php

namespace App\Forms;

use App\Models\UserModel;

class LoginFactory //extends CustomFormFactory
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

    public function renderForm()
    {
        $colors = $this->forms->colors();
        $randColor = $colors[0];
        $colorKey = $colors[1];
        
        $form = $this->forms->create();        

        $form->addHidden('colorRand', $colorKey)
            ->setHtmlAttribute('id', 'colorRand')
            ->setOmitted();

        $form->addText('email', 'Email:')
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->addRule($form::EMAIL)
            ->addRule($form::MIN_LENGTH, 'Name has to be minimum of %d letters', 2)
            ->setHtmlAttribute('class', 'form-control');
        $form->addPassword('password', 'Password:')
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->setHtmlAttribute('class', 'form-control');

        $form->addRadioList('colors', 'Select '.$randColor.' Color:', $this->forms->colors)
            ->setHtmlAttribute('class', 'radio')
            ->addRule($form::EQUAL, 'Wrong color', $form['colorRand'])
            ->setOmitted();

        $form->addSubmit('submit', 'Submit')
            ->setHtmlAttribute('class', 'btn btn-primary');
        return $form;
    }

}