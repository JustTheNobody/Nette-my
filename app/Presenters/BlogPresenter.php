<?php

declare(strict_types=1);

namespace App\Presenters;

use app\Models\BlogModel;
use App\Models\UserModel;
use App\Forms\BlogFactory;
use Nette\Utils\ArrayHash;
use App\Models\StatisticModel;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Controls\HiddenField;
use Nette\Application\BadRequestException;

/**
 * Blog Presenter
 * @package App\Presenters
 */
final class BlogPresenter extends Presenter
{
    const
    FORM_MSG_REQUIRED = 'This field is required';

    public UserModel $user;

    public string $status = '';
    //Home page Blog => last added -> by id?
    private $defaultBlogId;
    public $result = '';

    /** @var BlogModel Blog Model. */
    private $blogModel;

    //for edit/delete
    public array $blogEdit = [];
    public string $blog = '';
    public int $blog_id = 0;
    public string $title = '';
    public string $content = '';
    public int $user_id = 0;
    public int $id = 0;
    
    public BlogFactory $forms;
    public StatisticModel $statistic;

     /**
     * Construct with default Blog id
     * @param int $defaultBlogId Blog ID
     * @param BlogModel $BlogModel Blog Model
     */
    public function __construct(
        string $defaultBlogId = null,
        BlogModel $blogModel,
        UserModel $user,
        BlogFactory $forms,
        StatisticModel $statistic
    ) {
        parent::__construct();
        $this->defaultBlogId = $defaultBlogId;
        $this->blogModel = $blogModel;
        $this->user = $user;
        $this->forms = $forms; 
        $this->statistic = $statistic;       
    }

    public function beforeRender()
    {
        $this->statistic->saveStatistic();
        $this->template->title = 'blog';
        if (!$this->user->checkAuth()) {
            $this->template->role['role'] = "guest";
        }
        
    } 
    
    private function check()
    {
        //check if loged in -> if not redirect
        if (!$this->user->checkAuth()) {
            $this->flashMessage('Sorry, it look like you are not loged in.', 'alert');
            $this->redirect('Login:default');
            exit;
        }
    }

    /**
     * Read the Default Blog template.
     * @param string|null $id Blog id
     * @throws BadRequestException if not found
     */
    public function renderDefault()
    {
        $blog = $this->blogModel->getBlogs();

        // Read the Blog -> 404 if not found.
        if (!$blog) {
            $this->flashMessage('There are not any blogs in here yet.', 'fail');
        }

        if ($this->user->checkAuth()) {
            $this->template->role = $this->getUser()->getIdentity()->roles;
        }
        
        $this->template->blog = $blog; // Send to template.
        $this->template->title = 'blog';
    }

    public function handleDelete($id)
    {
        $this->check();
        $item = explode('_', $id);
        $result = $this->blogModel->removeBlog($id);

        ($result)?
            $this->flashMessage("$item[0] has been deleted.", 'success'):
            $this->flashMessage("Sorry, there was a unexpected error in deleting the $item[0].", 'fail');
        
        $this->redirect('Blog:default');
    }
    
    /**
     * Add the Blog section
     */
    public function renderAdd()
    {
        $this->check();
        $this->template->title = 'blog';
    }

    protected function createComponentBlogForm()
    {
        $form = $this->forms->renderForm("");
        $form->onSuccess[] = [$this, 'blogFormSucceeded'];
        return $form;
    }

    public function blogFormSucceeded(ArrayHash $values)
    {
        if ($this->blogModel->saveBlog($values)) {
            if ($this->user->testUser->getIdentity()->getRoles()['role'] == 'admin') {
                $this->flashMessage('Blog has been saved.', 'success');
            } else {
                $this->flashMessage('Blog has been saved and will be visible after approvement, thank you.', 'success');
            }                        
        } else {
            $this->flashMessage('Sorry, there was a unexpected error in saving the Blog.', 'fail');
        }  
        $this->redirect('Blog:default');
    }

    /**
     * Edit the Blog section
     */
    public function renderEdit($blog)
    {   
        $this->check();
        $this->id = (int)$blog;
        $this->blogEdit = $this->blogModel->getBlog($this->id);
        $this->template->blog = (int)$this->blogEdit; // Send to template.
    }

    protected function createComponentEditForm()
    {
        $values = $this->user->request->getPost();

        if (!empty($values)) {
            $form = $this->forms->renderForm($values);
            $form->onSuccess[] = [$this, 'editFormSucceeded'];
        } else {
            $form = $this->forms->renderForm($this->blogEdit[$this->id]);
        }
        
        return $form;
    }

    public function editFormSucceeded(ArrayHash $blog)
    {
        if ($this->blogModel->updateBlog($blog)) {
            //redirect 2 userPage
            $this->status = "success";
            $this->flashMessage('Blog has been updated.', 'success');
        } else {
            //redirect
            $this->status = "fail";
            $this->flashMessage('Sorry, there was a unexpected error in updating of the blog.', 'fail');
        }
        $this->redirect('Blog:default');
    }

    protected function createComponentCommentForm()
    {
        $form = $this->forms->renderCommentForm();
        $form->onSuccess[] = [$this, 'commentFormSucceeded'];
        return $form;
    }

    public function commentFormSucceeded(ArrayHash $values)
    {
        if ($this->blogModel->commentBlog($values)) {
            if ($this->user->testUser->getIdentity()->getRoles()['role'] == 'admin') {
                $this->flashMessage('Your comment has been added.', 'success');
            } else {
                $this->flashMessage('Your comment has been added and will be visible after approvement, thank you.', 'success');
            }                        
        } else {
            $this->flashMessage('Sorry, there was a problem, your comment is not added.', 'fail');
        }  
        $this->redirect('Blog:default');          
    }
    
    protected function createComponentCommentEditForm()
    {
        $form = $this->forms->renderCommentEditForm();
        $form->onSuccess[] = [$this, 'commentEditFormSucceeded'];
        return $form;
    }

    public function commentEditFormSucceeded(ArrayHash $values)
    {
        ($this->blogModel->updateBlog($values))? 
        $this->flashMessage('Your comment has been updated.', 'success'):
        $this->flashMessage('Sorry, there was a problem, your comment has not been updated.', 'fail');
        $this->redirect('Blog:default');
    }

    public function handleAprove($item, $id)
    {
        $this->blogModel->aprove($item, $id);
    }

}
