<?php

declare(strict_types=1);

namespace app\Models;

use App\Models\EmailModel;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

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
    protected $user;
    public EmailModel $emailS;

    public function __construct(UserModel $user, EmailModel $emailS)
    {
        $this->user = $user; 
        $this->emailS = $emailS;
    }

    //get Blog with bigest ID if there any Blog at all
    public function getLast()
    {
        $maxId = $this->user->database->fetchAll('SELECT article_id FROM articles WHERE aproved = 1');

        if (empty($maxId)) {
            return [];
        } else {
            $id = $maxId[count($maxId)-1]->article_id;
            $row = $this->user->database->fetchAll(
                "SELECT * FROM articles
                WHERE article_id = $id"
            );

            $row[0]->created_at = $row[0]->created_at->format('d.m.Y');
            return $row;
        }
    }

    //get Blog by id
    public function getBlog($id)
    {
        //return $this->database->table(self::ARTICLE_TABLE_NAME)->where(self::ARTICLE_COLUMN_ID, $id)->fetch();
        return  (array)$this->user->database
            ->table(self::ARTICLE_TABLE_NAME)
            ->fetchAssoc('article_id', $id);
    }

    /**
     * Save Blog
     * @param array|ArrayHash $blog
     */
    public function saveBlog(ArrayHash $blog)
    {
        $this->title = $blog->title;
        $this->content = $blog->content;

        $this->user->database->query(
            'INSERT INTO articles ?', [ 
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => $this->user->testUser->getId(),
            'aproved' => ($this->user->testUser->getIdentity()->getRoles()['role'] == 'admin')? 1:null
            ]
        );

        // it return's auto-increment of the inserted blog
        $id = $this->user->database->getInsertId();

        //send email 
        ($id > 0)?? $this->emailS->sendBlogPost($blog, 'article');

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
            $query = $this->user->database->query(
                'DELETE FROM comments WHERE ?', [
                'comment_id' => $this->artId]
            );
        } else {
            //remove Article & comments
            $query = $this->user->database->query(
                'DELETE FROM articles WHERE ?', [
                self::ARTICLE_COLUMN_ID => $this->artId]
            );
            $this->user->database->query(
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
        if ($values['edit'] == "article") {
            $query = $this->user->database->query(
                'UPDATE articles SET', [
                'title' => $values['title'],
                'content' => $values['content']
                ], 'WHERE article_id = ?', $values['article_id']
            );
        } else {
            $query = $this->user->database->query(
                'UPDATE comments SET', [
                'content' => $values['content']
                ], 'WHERE comment_id = ?', $values['comment_id']
            );
        }
        return ($query->getRowCount() !== 1) ? false : true;
    }

    public function commentBlog($values)
    {
        
        $this->content = $values->content;
        $this->artId = $values->article_id;
        $this->comId = $values->comment_id;    

            $this->user->database->query(
                'INSERT INTO comments ?', [ 
                'article_id' => $this->artId,
                'parent_id' => $this->comId,
                'content' => $this->content,
                'user_id' => $this->user->testUser->getId(),
                'aproved' => ($this->user->testUser->getIdentity()->getRoles()['role'] == 'admin')? 1:null]
            );
        // it return's auto-increment of the inserted blog
        $id = $this->user->database->getInsertId();

        return ($id > 0) ? true : false;
    }

    //get all Blog -> display one at home page rest in Blog page
    public function getBlogs()
    {    
        if (!$this->user->testUser->getIdentity() || $this->user->testUser->getIdentity()->getRoles()['role'] != 'admin') {
            $row = $this->user->database->fetchAll('SELECT * FROM articles WHERE aproved = 1 ORDER BY article_id DESC');
            $rowComment = $this->user->database->fetchAll('SELECT * FROM comments WHERE aproved = 1 ORDER BY parent_id DESC');
        } else {            
            $row = $this->user->database->fetchAll('SELECT * FROM articles ORDER BY article_id DESC');
            $rowComment = $this->user->database->fetchAll('SELECT * FROM comments ORDER BY parent_id DESC');
        }

        if (empty($row)) {
            return false;
        }
        //sort by article's comments
        if ($rowComment) {
            return self::relations($row, $rowComment);
        } else {
            foreach ($row as &$item) {                              
                $item = (array)$item;
                $item['created_at'] = $item['created_at']->format('d.m.Y');
            }
            return $row;
        }
    }

    public static function relations($row, $rowComment)
    {
               
        foreach ($rowComment as $rowComm) {
            $rowComm->created_at = $rowComm->created_at->format('d.m.Y'); 
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
            $rEntry['created_at'] = $rEntry['created_at']->format('d.m.Y'); 
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

    public function aprove($item, $id)
    {
        //get the last char off
        $helpItem = substr($item, 0, -1);

        $query = $this->user->database->query(
            'UPDATE ' . $item . ' SET', [
            'aproved' => 1
            ], 'WHERE '.$helpItem.'_id = ?', $id
        );
    
        return ($query->getRowCount() !== 1) ? false : true; 
    }
}
