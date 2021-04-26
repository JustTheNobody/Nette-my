<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Models\StatisticModel;


final class Error4xxPresenter extends Nette\Application\UI\Presenter
{
	public StatisticModel $statistic;

	public function __construct(StatisticModel $statistic)
    {
        $this->statistic = $statistic;
    }

	public function startup(): void
	{
		parent::startup();
		if (!$this->getRequest()->isMethod(Nette\Application\Request::FORWARD)) {
			$this->error();
		}
	}


	public function renderDefault(Nette\Application\BadRequestException $exception): void
	{
		// load template 403.latte or 404.latte or ... 4xx.latte
		$this->statistic->saveErrorStatistic($exception->getCode());
		$file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
		$this->template->setFile(is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte');
	}
}
