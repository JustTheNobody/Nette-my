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
            'UPDATE statistic_pages SET ?', [                
            'page_count' => ($this->database->query("SELECT page_count FROM statistic_pages WHERE page_name=?", $url[1])->fetchField()) +1
            ], 'WHERE page_name = ?', $url[1]
        );
    }

    public function saveErrorStatistic($error)
    {
        // 1. check if the error exist in DB

        //if exists => +1

        // if doesn't exist => create record with +1

        $this->database->query(
            'UPDATE statistic_error SET ?', [                
            'count' => ($this->database->query("SELECT count FROM statistic_error WHERE error=?", $error)->fetchField()) +1
            ], 'WHERE error = ?', $error
        );
    }

    public function getPageStatistic()
    {
        $statistic_page = [];

        $data_page = (array)$this->database->fetchAll('SELECT * FROM statistic_pages ORDER BY page_count DESC');
        for ($i=0; $i<count($data_page); $i++) {
            $statistic_page[ucfirst($data_page[$i]->page_name)] = $data_page[$i]->page_count;
        }

        return $statistic_page;
    }

    public function getErrorStatistic()
    {
        $statistic_error = [];
        $data_error = (array)$this->database->fetchAll('SELECT * FROM statistic_error WHERE count>0 ORDER BY count DESC');

        for ($i=0; $i<count($data_error); $i++) {
            $statistic_error[$data_error[$i]->error] = $data_error[$i]->count;
        }

        return $statistic_error;
    }
}