<?php

namespace App\Forms;

use App\Models\UserModel;
use Nette\Utils\Validators;

use Nette\Security\UserStorage;

class PortfolioForm
{
    const
    FORM_MSG_REQUIRED = 'this field is required';

    public CustomFormFactory $forms;

    public function __construct(CustomFormFactory $forms)
    {
        $this->forms = $forms;
    }

    public function renderEditPortfolioForm($values)
    {        
        $form = $this->forms->create();

        $form->addHidden('portfolio_id')
            ->setValue(isset($values)?$values['portfolio_id']: '');
        $form->addHidden('category')
            ->setValue(isset($values)? lcfirst($values['category']): '');
    
        $form->addText('title', 'Title')
            ->setValue(isset($values)?$values['title']: '')
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('description', 'Description')
            ->setValue(isset($values)?$values['description']: '')
            ->setHtmlAttribute('class', 'form-control');
        $form->addTextArea('content', 'Content')
            ->setValue(isset($values)?$values['content']: '')
            ->setHtmlAttribute('class', 'form-control');

        $form->addUpload('img', 'New Img:') 
            ->setValue(isset($values)?$values['img']: '')           
            ->setHtmlAttribute('onchange', 'previewFile(this)');

        $form->addSubmit('submit', 'Update')
            ->setHtmlAttribute('class', 'btn btn-primary');
        return $form;
    }

}