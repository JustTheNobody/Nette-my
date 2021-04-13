<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\UserModel;
use Nette\Utils\ArrayHash;
use App\Forms\PortfolioForm;
use app\Models\PortfolioModel;
use App\Models\StatisticModel;
use Nette\Application\UI\Presenter;

final class PortfolioPresenter extends Presenter
{
    protected PortfolioModel $portfolio;
    public UserModel $user;
    public PortfolioForm $pForm;
    public $values;
    public StatisticModel $statistic;
    public $role = '';

    public function __construct(
        UserModel $user,
        PortfolioModel $portfolio,
        PortfolioForm $pForm,
        StatisticModel $statistic
    ) {
        $this->user = $user;
        $this->portfolio = $portfolio;  
        $this->pForm = $pForm; 
        $this->statistic = $statistic; 
    }

    public function beforeRender()
    {
        $this->statistic->saveStatistic();
        $this->template->title = 'portfolio'; 
        if (!$this->user->checkAuth()) {
            $this->template->role = "guest";
        }
    }

    private function check()
    {
        //check if loged in -> if not redirect
        if (!$this->user->checkAuth()) {
            $this->flashMessage('Sorry, it look like you are not loged in.', 'alert');
            $this->redirect('Login:default');
            exit;
        }
    }

    public function renderDefault(array $value)
    {    

        if ($this->user->checkAuth()) {
            $this->template->role = $this->getUser()->getIdentity()->roles;
        } 
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

    public function handleDelete($id, $img, $category)
    {    
        $this->check(); 
        $result = $this->portfolio->remove($id, $img, $category);

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