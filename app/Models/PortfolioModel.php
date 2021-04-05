<?php

declare(strict_types=1);

namespace app\Models;

use DateTime;
use stdClass;
use Nette\SmartObject;
use Nette\Database\Explorer;

class PortfolioModel
{
    use SmartObject;

    const
    PORTFOLIO_TABLE_NAME = 'portfolio',
    PORTFOLIO_COLUMN_ID = 'portfolio_id';

    public function __construct(Explorer $database, UserModel $user)
    {
        $this->database = $database;
        $this->user = $user;        
    }

    //get Blog with bigest ID if there any Blog at all
    public function getLast()
    {
        $maxId = $this->database->fetchAll('SELECT portfolio_id FROM portfolio');

        if (empty($maxId)) {
            return false;
        } else {
            $row = $this->database->fetchAll(
                "SELECT * FROM portfolio
                ORDER BY portfolio_id DESC LIMIT 1"
            );
            return $row;
        }
    }

    public function getReferences()
    {
        $row = $this->database->fetchAll('SELECT * FROM portfolio ORDER BY portfolio_id DESC');
        $rowCategory = $this->database->fetchAll('SELECT * FROM category');
     
        if (empty($row)) {
            return false;
        }
        //sort by article's comments
        $output = self::relations($row, $rowCategory);

        return $output;
    }


    //TODO
    public static function relations($row, $rowComment)
    {

        foreach ($rowComment as $rowComm) {
            $rowComm->created_at = $rowComm->created_at->format('d-m-Y');           
            $newRow[] = (array) $rowComm;
        }

        $references = [];
        foreach ($newRow as &$entry) {
            $entry['comments'] = [];
            $references[$entry['comment_id']] = &$entry;                      
        } 

        $output = [];
        array_walk(
            $references, function (&$entry) use ($references, &$output) {
                if ($entry['parent_id'] != 0) {
                    $references[$entry['parent_id']]['comments'][] = $entry;
                } else {
                    $output[] = $entry;
                }
            }
        );                
    
        return $references;
    }
}