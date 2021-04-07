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

    private Explorer $database;
    public FileModel $file;

    public function __construct(
        Explorer $database,
        FileModel $file
    ) {
        $this->database = $database; 
        $this->file = $file;      
    }

    public function getLast()
    {        
        $row = $this->database->fetchAll(
            "SELECT * FROM portfolio
            ORDER BY portfolio_id DESC LIMIT 1"
        );

        $row[0]->created_at = $row[0]->created_at->format('d-m-Y');
        //get the category by the category_id  
        $category = $this->database->fetchPairs(
            "SELECT category FROM category
            WHERE category_id = ?", $row[0]->category_id
        );
        $row[0]->category = ucfirst($category[0]);
   
        return (array)$row[0];
    }

    public function getOne($id)
    {        
        $row = $this->database->fetchAll(
            "SELECT * FROM portfolio
            WHERE portfolio_id = ?", $id
        );
   
        return (array)$row[0];
    }

    public function getReferences()
    {
        $rows = $this->database->fetchAll('SELECT * FROM portfolio ORDER BY category_id');
        $rowCategory = $this->database->fetchAll('SELECT * FROM category');
  
        if (empty($rows)) {
            return false;
        }
        
        return self::relations($rows, $rowCategory);
    }


    //TODO
    public static function relations($rows, $rowCategory)
    {
        foreach ($rows as &$row) {
            $row->created_at = $row->created_at->format('d-m-Y');  
            $row = (array)$row;        
        }
 
        $newP = [];
        array_walk(
            $rows, function (&$category) use (&$rowCategory, &$newP) {
                foreach ($rowCategory as $cat) {
                    if ($cat->category_id == $category['category_id']) { 
                        $newP[$cat->category][] = $category;
                    }                    
                }                                
            }
        ); 
        return $newP;
    }

    public function remove($id)
    {
        $query = $this->database->query(
            'DELETE FROM portfolio WHERE portfolio_id = ?', $id
        );
        return ($query->getRowCount() !== 1) ? false : true;
    }

    public function save($value)
    {

        //check if there is new img  2 upload
        if ($value['img']->hasFile()) {

            $newName = $this->file->upload($value['img'], $value['category']);

            $query = $this->database->query(
                'UPDATE portfolio SET', [
                'title' => $value['title'],
                'description' => $value['description'],
                'content' => $value['content'],
                'img' => $newName,
                ], 'WHERE portfolio_id = ?', $value['portfolio_id']
            );
        } else {
            $query = $this->database->query(
                'UPDATE portfolio SET', [
                'title' => $value['title'],
                'description' => $value['description'],
                'content' => $value['content'],
                ], 'WHERE portfolio_id = ?', $value['portfolio_id']
            );
        }
              
        return ($query->getRowCount() !== 1) ? false : true;
    }
}