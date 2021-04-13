<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Models\BlogModel;
use App\Models\PortfolioModel;
use App\Models\StatisticModel;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    protected $blogModel = [];
    protected array $lastBlog = [];
    protected PortfolioModel $portfolioModel;
    protected array $portfolio = [];
    public StatisticModel $statistic;

    public function __construct(
        BlogModel $blogModel,
        PortfolioModel $portfolioModel,
        StatisticModel $statistic
    ) {
        //parent::__construct();
        $this->blogModel = $blogModel;
        $this->portfolioModel = $portfolioModel;
        $this->statistic = $statistic;
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
}
