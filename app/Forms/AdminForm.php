<?php

namespace App\Forms;

use App\Models\UserModel;
use Nette\Utils\Validators;

use Nette\Security\UserStorage;

class AdminForm
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
        $form = $this->forms->create();

        $form->addHidden('category')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('readonly');
        $form->addText('title', 'Title:')
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('description', 'Description:')
            ->setHtmlAttribute('class', 'form-control');
        $form->addTextArea('content', 'Content:')
            ->setHtmlAttribute('rows', 10)
            ->setHtmlAttribute('cols', 40)
            ->setHtmlAttribute('class', 'form-control');        

        $form->addUpload('file', 'Choose file:')
            ->addRule($form::IMAGE, 'Avatar musí být JPEG, PNG, GIF or WebP.')
            ->addRule($form::MAX_FILE_SIZE, 'Maximální velikost je 1 MB.', 1024 * 1024)
            ->setHtmlAttribute('onchange', 'previewFile(this)');

        $form->addSubmit('submit', 'Add')
            ->setHtmlAttribute('class', 'btn btn-primary');   
                     
        return $form;
    }
}