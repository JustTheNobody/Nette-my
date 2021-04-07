<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;
use Nette\Application\Request;

class StatisticModel implements Request
{
    private Explorer $database;
    public Request $request;

    public function __construct(Explorer $database, Request $request) 
    {
        $this->database = $database;
        $this->request = $request;
    }

    private function saveStatistic($page)
    {
        //save to DB
        echo "save statistic to DB";
    }
}