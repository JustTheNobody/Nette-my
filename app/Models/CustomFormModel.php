<?php

namespace App\Models;

use App\Forms\CustomFormFactory;

class CustomFormModel //extends CustomFormFactory
{

    const
    FORM_MSG_REQUIRED = 'this field is required';
    
    private $auth = ['login', 'register'];
    private $article = ['add'];
    public $forms;

    public function __construct(CustomFormFactory $form)
    {
        $this->forms = $form;
    }

    public function renderForm($path)
    {

        $form = $this->forms->create();

        if (in_array($path, $this->auth)) {
            $form->addText('f_name', 'First Name:')
                ->setRequired(self::FORM_MSG_REQUIRED)
                ->setHtmlAttribute('class', 'form-control');
            $form->addPassword('password', 'Password:')
                ->setRequired(self::FORM_MSG_REQUIRED)
                ->setHtmlAttribute('class', 'form-control');
        }

        $form->addSubmit('submit', 'Submit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        return $form;
    }

    public function addEmail()
    {
        //return
    }

    public function addName($name)
    {
        //return 
    }

    

}
