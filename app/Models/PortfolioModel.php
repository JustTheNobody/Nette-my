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

        if ($row) {
            $row[0]->created_at = $row[0]->created_at->format('d-m-Y');
            //get the category by the category_id  
            $category = $this->database->fetchPairs(
                "SELECT category FROM category
                WHERE category_id = ?", $row[0]->category_id
            );
            $row[0]->category = ucfirst($category[0]);

            return (array)$row[0];
        }
        
        return [];        
    }

    public function getOne($id)
    {        
        $row = $this->database->fetchAll(
            "SELECT * FROM portfolio
            WHERE portfolio_id = ?", $id
        );
        $rowSub = $this->database->fetchAll(
            "SELECT * FROM category
            WHERE category_id = ?", $row[0]->category_id
        );

        $row[0]->sub_category = $rowSub[0]->sub_category;
        $row[0]->category = $rowSub[0]->category;

        return (array)$row[0];
    }

    public function getReferences()
    {
        $rows = $this->database->fetchAll('SELECT * FROM portfolio ORDER BY category_id');
        $rowCategory = $this->database->fetchAll('SELECT * FROM category ORDER BY category');

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
            $row->category = '';
            $row = (array)$row;        
        }
 
        $newP = [];
        array_walk(
            $rows, function (&$category) use (&$rowCategory, &$newP) {
                foreach ($rowCategory as $cat) {
                    if ($cat->category_id == $category['category_id']) { 
                        $category['category'] = $cat->category;
                        $category['sub_category'] = $cat->sub_category;
                        $newP[$cat->category][] = $category;                        
                    }                    
                }                                
            }
        ); 
        return $newP;
    }

    public function remove($id, $fileName, $category)
    {
        //delete img from folder!  fileName + filePAth
        $this->file->deleteFile($fileName, $category);

        $query = $this->database->query(
            'DELETE FROM portfolio WHERE portfolio_id = ?', $id
        );
        return ($query->getRowCount() !== 1) ? false : true;
    }

    public function save($value, $file)
    {
        if ($value['sub_category'] != $value['sub_category_old']) {
            $this->database->query(
                'UPDATE category SET', [
                'sub_category' => $value['sub_category']
                ], 'WHERE category_id = ?', $this->database->fetchField('SELECT category_id FROM portfolio WHERE portfolio_id =?', $value['portfolio_id'])
            );
        }
        //check if there is new img  2 upload
        if ($file != null) {

            //delete the old img from folder!  fileName + filePAth
            $this->file->deleteFile($value['oldImgName'], $value['category']);

            $newName = $this->file->upload($file, $value['category']);

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

    public function getSubCategories($category)
    {
        //where('sub_category != "" AND category='.$category)
        $row =  $this->database->table('category')->where('sub_category != "main"')->where('category=?', $category)->fetchAssoc('sub_category');
        $subCategorylist['main'] = 'Select';
        foreach ($row as $key =>$value) {
            $subCategorylist[$key] = ucfirst($key);
        }
        
        return $subCategorylist;
    }

    public function saveAdd($values, $file)
    {

        // 1 - get the file name to be passed to DB -> or rename based on timestamp?
        // 2 - upload file to storage folder based on ['types']
        $imgName = $this->file->upload($file, $values['category']);

        if (\is_string($imgName)) {
            
            $subQuery = $this->database->table('category')->select('category_id')->where('category =?', $values['category'])->fetchAssoc('category_id');

            // 3 - save 2 Db based on ['types']
            $this->database->query(
                'INSERT INTO portfolio ?', [
                'title' => $values['title'],
                'description' => $values['description'],
                'content' => $values['content'],
                'img' => $imgName,
                'category_id' => array_key_first($subQuery)]
            );

            //old ->  $this->database->fetchField('SELECT category_id FROM category WHERE category = ? AND sub_category= ?', $values['category'], $values['sub_category'])
//            $this->database->table('category')->where('sub_category !=', $values['sub_category'])->where('category=?', $values['category'])->fetchAssoc('sub_category')->fetchField('category_id');




            // return auto-increment of the inserted row
            return $this->database->getInsertId();

        } else {
            return false;            
        }     
    }
}