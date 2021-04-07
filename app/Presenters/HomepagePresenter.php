<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Models\BlogModel;
use App\Models\PortfolioModel;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    private $blogModel = [];
    private array $lastBlog = [];
    private PortfolioModel $portfolioModel;
    private array $portfolio = [];

    public function __construct(BlogModel $blogModel, PortfolioModel $portfolioModel)
    {
        //parent::__construct();
        $this->blogModel = $blogModel;
        $this->portfolioModel = $portfolioModel;
    }

    //get last Article
    public function beforeRender()
    {
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
