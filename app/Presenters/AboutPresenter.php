<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Presenter;

final class AboutPresenter extends Presenter
{
    public function beforeRender()
    {
        $this->template->title = 'about'; 
    }

    public function renderDefault()
    {
        //
    }

}
