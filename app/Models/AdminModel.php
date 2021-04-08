<?php

namespace App\Models;

use stdClass;
use Nette\Utils\Image;
use Nette\Utils\FileSystem;
use Nette\Database\Explorer;

class AdminModel
{
    public Explorer $database;
    public UserModel $user;
    public FileModel $file;

    public function __construct(Explorer $database, UserModel $user, FileModel $file) 
    {
        $this->database = $database;
        $this->user = $user;
        $this->file = $file;
    }

    public function saveAdd($values, $file)
    {
        // 1 - get the file name to be passed to DB -> or rename based on timestamp?
        // 2 - upload file to storage folder based on ['types']
        $imgName = $this->file->upload($file, $values['category']);

        if (\is_string($imgName)) {
            
            // 3 - save 2 Db based on ['types']
            $this->database->query(
                'INSERT INTO portfolio ?', [
                'title' => $values['title'],
                'description' => $values['description'],
                'content' => $values['content'],
                'img' => $imgName,
                'category_id' => $this->database->fetchField('SELECT category_id FROM category WHERE category = ?', $values['category'])]
            );
            // return auto-increment of the inserted row
            return $this->database->getInsertId();

        } else {
            return false;            
        }     
    }
}