<?php

declare(strict_types=1);

namespace App\Models;

use Nette\Database\Explorer;

class StatisticModel
{
    protected Explorer $database;
    public UserModel $user;

    public function __construct(Explorer $database, UserModel $user) 
    {
        $this->database = $database;
        $this->user = $user;
    }

    public function saveStatistic()
    {
        $url = $this->user->request->getUrl()->path;
        $url = \explode("/", $url);

        if ($url[1] == "") { 
            $url[1] = "home";
        } 

        $this->database->query(
            'UPDATE statistic SET ?', [                
            'page_count' => ($this->database->query("SELECT page_count FROM statistic WHERE page_name=?", $url[1])->fetchField()) +1
            ], 'WHERE page_name = ?', $url[1]
        );
    }
}