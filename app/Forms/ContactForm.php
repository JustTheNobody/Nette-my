<?php

namespace App\Forms;

use App\Models\UserModel;
use Nette\Utils\Validators;

class ContactForm
{
    const
    FORM_MSG_REQUIRED = 'this field is required',
    FORM_MSG_EMAIL = 'invalid email address';

    public CustomFormFactory $forms;
    public UserModel $user;

    public function __construct(UserModel $user, CustomFormFactory $forms)
    {
        $this->forms = $forms;
        $this->user = $user;
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
        $form->addText('subject', 'Subject:')
            ->addRule($form::MIN_LENGTH, 'Subject has to be minimum of %d letters', 5)
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->setHtmlAttribute('class', 'form-control');
        if (!$this->user->testUser->getIdentity()) {
            $form->addEmail('email', 'Email:')
                ->setRequired(self::FORM_MSG_REQUIRED)
                ->addRule($form::EMAIL)
                ->addRule(
                    function ($item) {
                        if (!Validators::isEmail($item->value)) {
                            return false;
                        }
                        return true;
                    }, "This email is not valid"
                )
                // check unique record in form
                ->setHtmlAttribute('class', 'form-control');
        } else {
            $form->addEmail('email', 'Email:')
                ->setDefaultValue($this->user->getEmailValue()) //$this->user->testUser->getIdentity()->email  ??
                ->setHtmlAttribute('class', 'form-control')
                ->setHtmlAttribute('readonly');
        }
    
        $form->addTextArea('body', 'Body:')
            ->setHtmlAttribute('rows', 10)
            ->setHtmlAttribute('cols', 40)
            ->addRule($form::MIN_LENGTH, 'Name has to be minimum of %d letters', 10)
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->setHtmlAttribute('class', 'form-control');  

        $form->addRadioList('colors', 'Select '.$randColor.' Color:', $this->forms->colors)
            ->setHtmlAttribute('class', 'radio')
            ->addRule($form::EQUAL, 'Wrong color', $form['colorRand'])
            ->setOmitted();

        $form->addSubmit('submit', 'Submit')
            ->setHtmlAttribute('class', 'btn btn-primary g-recaptcha');
        return $form;
    }
}
