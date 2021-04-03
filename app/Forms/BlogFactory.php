<?php

namespace App\Forms;

use App\Models\UserModel;

class BlogFactory
{

    const
    FORM_MSG_REQUIRED = 'this field is required';
    
    public CustomFormFactory $forms;
    public UserModel $user;

    public function __construct(CustomFormFactory $forms, UserModel $user)
    {
        $this->forms = $forms;
        $this->user = $user;
    }

    public function renderForm($blog)
    {
        $form = $this->forms->create();
                
        $form->addHidden('article_id', (is_array($blog) && !empty($blog))? $blog['article_id'] : '');
    
        $form->addText('title', 'Title')
            ->setValue((is_array($blog) && !empty($blog))? $blog['title'] : '')
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->addRule($form::MIN_LENGTH, 'Title has to be minimum of %d letters', 5)
            ->addRule($form::MAX_LENGTH, 'Title has to be maximum of %d letters', 25);

        $form->addTextArea('content', 'Content')
            ->setValue((is_array($blog) && !empty($blog))? $blog['content'] : '')
            ->setHtmlAttribute('rows', 10)
            ->setHtmlAttribute('cols', 40)
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->addRule($form::MIN_LENGTH, 'Content has to be minimum of %d letters', 30);

        $form->addSubmit('submit', (is_array($blog) && !empty($blog))? 'Update Blog' : 'Add Blog')
            ->setHtmlAttribute('class', 'btn btn-primary');
        $form->setHtmlAttribute('class', 'updateForm');

        return $form;
    }

    public function renderCommentForm()
    {
        $form = $this->forms->create();
        $form->addHidden('article_id');
        $form->addHidden('comment_id');

        $form->addTextArea('content', 'Content')
            ->setHtmlAttribute('rows', 5)
            ->setHtmlAttribute('cols', 40)            
            ->setRequired(self::FORM_MSG_REQUIRED)
            ->addRule($form::MIN_LENGTH, 'Content has to be minimum of %d letters', 5);
            
        $form->addSubmit('submit', 'add Comment')
            ->setHtmlAttribute('class', 'btn btn-primary');
        $form->setHtmlAttribute('class', 'updateForm');
        
        return $form;
    }
}