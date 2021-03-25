<?php

namespace App\Forms;

use Nette\Utils\Validators;

class ContactForm
{
    const
    FORM_MSG_REQUIRED = 'this field is required',
    FORM_MSG_EMAIL = 'invalid email address';

    public CustomFormFactory $forms;

    public function __construct(CustomFormFactory $forms)
    {
        $this->forms = $forms;
    }

    public function renderForm()
    {
        $form = $this->forms->create();
           
        $form->addText('subject', 'Subject:')
            ->addRule($form::MIN_LENGTH, 'Subject has to be minimum of %d letters', 5)
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->setHtmlAttribute('class', 'form-control');
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

        $form->addTextArea('body', 'Body:')
            ->setHtmlAttribute('rows', 10)
            ->setHtmlAttribute('cols', 40)
            ->addRule($form::MIN_LENGTH, 'Name has to be minimum of %d letters', 10)
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('submit', 'Submit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        return $form;
    }
}
