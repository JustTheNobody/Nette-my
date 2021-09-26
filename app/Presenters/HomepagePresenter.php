<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Models\BlogModel;
use App\Models\UserModel;
use App\Models\PortfolioModel;
use App\Models\StatisticModel;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    protected $blogModel = [];
    protected array $lastBlog = [];
    protected PortfolioModel $portfolioModel;
    protected array $portfolio = [];
    public StatisticModel $statistic;
    public UserModel $user;

    public function __construct(
        BlogModel $blogModel,
        PortfolioModel $portfolioModel,
        StatisticModel $statistic,
        UserModel $user
    ) {
        //parent::__construct();
        $this->blogModel = $blogModel;
        $this->portfolioModel = $portfolioModel;
        $this->statistic = $statistic;
        $this->user = $user;
    }

    //get last Article
    public function beforeRender()
    {
        $this->statistic->saveStatistic();
        $this->lastBlog = $this->blogModel->getLast();
        $this->portfolio = $this->portfolioModel->getLast();
        $this->template->title = 'home';
    }

    public function renderDefault()
    {
        $this->template->blog = $this->lastBlog;
        $this->template->portfolio = $this->portfolio;
    }

    public function handleResendEmailLink()
    {
        $this->user->sendEmailConfirm(
            $this->user->testUser->getIdentity()->getId(),
            $this->user->testUser->getIdentity()->getData()['email']
        );
        $this->presenter->flashMessage('We have sended you confirmation email, the link will expire in 1 hour', 'success');
        $this->redirect('Setting:default');  
    }
}
