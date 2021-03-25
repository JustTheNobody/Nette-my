<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Models\BlogModel;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    private $blogModel = [];
    private array $lastBlog = [];

    public function __construct(BlogModel $blogModel)
    {
        //parent::__construct();
        $this->blogModel = $blogModel;
    }

    //get last Article
    public function beforeRender()
    {
        $this->lastBlog = $this->blogModel->getLast();
    }

    public function renderDefault()
    {
        $this->template->blog = $this->lastBlog;
    }
}
