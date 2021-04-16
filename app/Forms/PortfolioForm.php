<?php

namespace App\Forms;

use App\Models\UserModel;
use Nette\Utils\Validators;

use app\Models\PortfolioModel;
use Nette\Security\UserStorage;

class PortfolioForm
{
    
    const
    FORM_MSG_REQUIRED = 'this field is required';

    public CustomFormFactory $forms;
    public PortfolioModel $portfolio;

    public function __construct(CustomFormFactory $forms, PortfolioModel $portfolio)
    {
        $this->forms = $forms;
        $this->portfolio = $portfolio;
    }

    public function renderEditPortfolioForm($values)
    {                
        $form = $this->forms->create();

        $form->addHidden('portfolio_id')
            ->setValue(isset($values)?$values['portfolio_id']: '');
        $form->addHidden('category')
            ->setValue(isset($values)? lcfirst($values['category']): '');
        $form->addHidden('sub_category_old')
            ->setValue(isset($values)? lcfirst($values['sub_category']): '');
        $form->addHidden('oldImgName')
            ->setValue(isset($values)? lcfirst($values['img']): '');
    
        if (isset($values) && $values['category'] == 'graphic' && $values['sub_category'] != 'main') {
            $form->addSelect('sub_category', 'Sub Category', $this->portfolio->getSubCategories('graphic'))
                ->setDefaultValue($values['sub_category']) 
                ->setHtmlAttribute('class', 'form-control');
        }

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

    public function renderForm($subCategorylist)
    {
        $form = $this->forms->create();

        $form->addHidden('category')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('readonly');

        if (!empty($subCategorylist)) {
            $form->addSelect('sub_category', 'Sub Category', $subCategorylist)
                ->setHtmlAttribute('class', 'form-control'); 
        }
                   
        $form->addText('title', 'Title:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired(self::FORM_MSG_REQUIRED);
        $form->addText('description', 'Description:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired(self::FORM_MSG_REQUIRED);
        if (empty($subCategorylist)) {
            $form->addTextArea('content', 'Content:')
                ->setHtmlAttribute('rows', 10)
                ->setHtmlAttribute('cols', 40)
                ->setHtmlAttribute('class', 'form-control')
                ->setRequired(self::FORM_MSG_REQUIRED);       
        } else {
            $form->addHidden('content');
        }
        $form->addUpload('file', 'Choose file:')
            ->addRule($form::IMAGE, 'Avatar musí být JPEG, PNG, GIF or WebP.')
            ->addRule($form::MAX_FILE_SIZE, 'Maximální velikost je 1 MB.', 1024 * 1024)
            ->setHtmlAttribute('onchange', 'previewFile(this)')
            ->setRequired(self::FORM_MSG_REQUIRED);

        $form->addSubmit('submit', 'Add')
            ->setHtmlAttribute('class', 'btn btn-primary');   
                     
        return $form;
    }

}