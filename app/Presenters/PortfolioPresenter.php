<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\UserModel;
use Nette\Utils\ArrayHash;
use App\Forms\PortfolioForm;
use app\Models\PortfolioModel;
use Nette\Application\UI\Presenter;

final class PortfolioPresenter extends Presenter
{
    private PortfolioModel $portfolio;
    public UserModel $users;
    public PortfolioForm $pForm;
    public $values;

    public function __construct(
        UserModel $users,
        PortfolioModel $portfolio,
        PortfolioForm $pForm
    ) {
        $this->users = $users;
        $this->portfolio = $portfolio;  
        $this->pForm = $pForm;  
    }

    public function beforeRender()
    {
        $this->template->title = 'portfolio'; 
        $this->template->role = $this->getUser()->getIdentity()->roles;   
    }

    public function renderDefault(array $value)
    {
        //get all
        $this->template->references = $this->portfolio->getReferences();
        //delete/edit
        $this->template->pAction = $value;
    }

    public function renderReference($category, $item, $edit)
    {       
        $this->template->reference = $this->portfolio->getOne($item);

        $this->values = $this->template->reference;
        $this->values['category'] = $category;

        $this->template->category = ucfirst($category);

        $this->template->edit = (!empty($edit))?? $edit;
    }

    public function handleDelete($id)
    {        
        $result = $this->portfolio->remove($id);

        ($result)?
            $this->flashMessage("Item has been deleted.", 'success'):
            $this->flashMessage("Sorry, there was a unexpected error in deleting the Item.", 'fail');
        
        $this->redirect('Portfolio:default');
    }

    protected function createComponentEditPortfolioForm()
    {

        $form = $this->pForm->renderEditPortfolioForm($this->values);

        $form->onSuccess[] = [$this, 'formSucces'];
        return $form;
    }

    public function formSucces(ArrayHash $values)
    {   
        
        $row = $this->portfolio->save($values);

        ($row && is_int($row))? 
        $this->flashMessage("Portfolio has not been updated!", 'fail') : 
        $this->flashMessage("Portfolio has been updated.", 'success');

        $this->redirect('Portfolio:default');  
    }

}