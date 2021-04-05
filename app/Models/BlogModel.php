<?php

declare(strict_types=1);

namespace app\Models;

use DateTime;
use stdClass;
use Nette\SmartObject;
use Nette\Utils\Arrays;
use Nette\Utils\ArrayHash;
use Nette\Database\Explorer;

class BlogModel
{
    use SmartObject;

    const
        ARTICLE_TABLE_NAME = 'articles',
        ARTICLE_COLUMN_ID = 'article_id';

    public string $blog = '';
    public int $blog_id = 0;
    public string $title = '';
    public string $content = '';
    public $artId = 0;
    public $comId = 0;
    private $user;

    private $database;

    public function __construct(Explorer $database, UserModel $user)
    {
        $this->database = $database;
        $this->user = $user;        
    }

    //get Blog with bigest ID if there any Blog at all
    public function getLast()
    {
        $maxId = $this->database->fetchAll('SELECT article_id FROM articles');

        if (empty($maxId)) {
            return false;
        } else {
            $id = $maxId[count($maxId)-1]->article_id;
            $row = $this->database->fetchAll(
                "SELECT * FROM articles
                WHERE article_id = $id"
            );

            $row[0]->created_at = $row[0]->created_at->format('d-m-Y');
            return $row;
        }
    }

    //get Blog by id
    public function getBlog($id)
    {
        return $this->database->table(self::ARTICLE_TABLE_NAME)->where(self::ARTICLE_COLUMN_ID, $id)->fetch();
    }

    /**
     * Save Blog
     * @param array|ArrayHash $blog
     */
    public function saveBlog(ArrayHash $blog)
    {
        $this->title = $blog->title;
        $this->content = $blog->content;

        $this->database->query(
            'INSERT INTO articles ?', [ 
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => $this->user->testUser->getId(),]
        );

        // it return's auto-increment of the inserted blog
        $id = $this->database->getInsertId();

        return ($id > 0) ? true : false;
    }

    /**
     * Remove Blog with given ID.
     */
    public function removeBlog($id)
    {
        $item = explode('_', $id);
        $this->artId = intval($item[1]);

        if ($item[0] == 'comment') {
            $query = $this->database->query(
                'DELETE FROM comments WHERE ?', [
                'comment_id' => $this->artId]
            );
        } else {
            //remove Article & comments
            $query = $this->database->query(
                'DELETE FROM articles WHERE ?', [
                self::ARTICLE_COLUMN_ID => $this->artId]
            );
            $query1 = $this->database->query(
                'DELETE FROM comments WHERE ?', [
                'article_id' => $this->artId]
            );
        }
        return ($query->getRowCount() !== 1) ? false : true;
    }

    /**
     * Update Blog with given ID.
     */
    public function updateBlog(object $values)
    {
        $query = $this->database->query(
            'UPDATE articles SET', [
            'title' => $values['title'],
            'content' => $values['content']
            ], 'WHERE article_id = ?', $values['article_id']
        );
      
        return ($query->getRowCount() !== 1) ? false : true;
    }

    public function commentBlog($values)
    {
        
        $this->content = $values->content;
        $this->artId = $values->article_id;
        $this->comId = $values->comment_id;    

            $this->database->query(
                'INSERT INTO comments ?', [ 
                'article_id' => $this->artId,
                'parent_id' => $this->comId,
                'content' => $this->content,
                'user_id' => $this->user->testUser->getId(),]
            );
        // it return's auto-increment of the inserted blog
        $id = $this->database->getInsertId();

        return ($id > 0) ? true : false;
    }

    //get all Blog -> display one at home page rest in Blog page
    public function getBlogs()
    {
        $row = $this->database->fetchAll('SELECT * FROM articles ORDER BY article_id DESC');
        $rowComment = $this->database->fetchAll('SELECT * FROM comments ORDER BY parent_id DESC');
     
        if (empty($row)) {
            return false;
        }
        //sort by article's comments
        $output = self::relations($row, $rowComment);

        return $output;
    }

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

        //put the article_id as key
        $referencesR = [];
        foreach ($row as &$rEntry) {
            $rEntry['comments'] = [];
            $rEntry['created_at'] = $rEntry['created_at']->format('d-m-Y'); 
            $referencesR[$rEntry['article_id']] = (array)$rEntry;
        }             
          
        \array_walk(
            $output, function (&$value) use (&$referencesR) {
                if ($referencesR[$value['article_id']] && $value['article_id']) {
                    $referencesR[$value['article_id']]['comments'][] = $value;
                }  
            }
        );     
        return $referencesR;
    }
}
