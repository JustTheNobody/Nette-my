<?php

declare(strict_types=1);

namespace app\Models;

use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use Nette\Database\Explorer;

class BlogModel
{
    use SmartObject;

    const
        TABLE_NAME = 'articles',
        COLUMN_ID = 'article_id';

    public string $blog = '';
    public int $blog_id = 0;
    public string $title = '';
    public string $description = '';
    public string $content = '';
    private $user;

    private $database;

    public function __construct(Explorer $database, UserModel $user)
    {
        $this->database = $database;
        $this->user = $user;
    }

    //get all Blog -> display one at home page rest in Blog page
    public function getBlogs()
    {
        $row = $this->database->fetchAll('SELECT * FROM articles ORDER BY article_id DESC');

        if (empty($row)) {
            return "No any blog here yet";
        }
        return $row;
    }

    //get Blog with bigest ID if there any Blog at all
    public function getLast()
    {
        $maxId = $this->database->fetchAll('SELECT article_id FROM articles');

        if (empty($maxId)) {
            return ["No blog"];
        } else {
            $id = $maxId[count($maxId)-1]->article_id;
            $row = $this->database->fetchAll(
                "SELECT * FROM articles
                WHERE article_id = $id"
            );
            return $row;
        }
    }

    //get Blog by id
    public function getBlog($id)
    {
        return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->fetch();
    }

    /**
     * Save Blog
     * @param array|ArrayHash $blog
     */
    public function saveBlog(ArrayHash $blog)
    {
        $this->title = $blog->title;
        $this->description = $blog->description;
        $this->content = $blog->content;

        $this->database->query(
            'INSERT INTO articles ?', [ 
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'user_id' => $this->user->testUser->getId(),]
        );

        // it return's auto-increment of the inserted blog
        $id = $this->database->getInsertId();

        return ($id > 0) ? "success" : "fail";
    }

    /**
     * Remove Blog with given ID.
     */
    public function removeBlog($blog_id)
    {
        $this->blog_id = intval($blog_id);

        $query = $this->database->query(
            'DELETE FROM articles WHERE ?', [
            self::COLUMN_ID => $this->blog_id,
            $this->user->testUser->getId()]
        );
        
        return ($query->getRowCount() !== 1) ? "fail" : "success";
    }

    /**
     * Update Blog with given ID.
     */
    public function updateBlog(object $values)
    {
        $query = $this->database->query(
            'UPDATE articles SET', [
            'title' => $values['title'],
            'content' => $values['content'],
            'description' => $values['description']
            ], 'WHERE article_id = ?', $values['article_id']
        );
      
        return ($query->getRowCount() !== 1) ? "fail" : "success";
    }
}
