<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\StatisticModel;
use Nette\Application\UI\Presenter;

final class AboutPresenter extends Presenter
{
    public StatisticModel $statistic;
    
    public function __construct(StatisticModel $statistic)
    {
        $this->statistic = $statistic;
    }

    public function beforeRender()
    {
        $this->template->title = 'about'; 

        $this->statistic->saveStatistic();
    }

    public function renderDefault($value)
    {
        $this->template->value = $value; 
    }

}
