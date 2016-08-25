<?php namespace Teb\SimpleCounsel\Components;

use Illuminate\Support\Facades\Log;
use Lang;
use Auth;
use Mail;
use Event;
use Flash;
use Input;
use File;
use Request;
use Redirect;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Exception;
use Teb\SimpleCounsel\Models\Category;
use Teb\SimpleCounsel\Models\Post;
use Teb\SimpleCounsel\Models\Comment;

class SimpleCounsel extends ComponentBase
{

  public $postPage;
  public $searchCategory;
  public $searchString;
  public $ownDisease;
  public $answerStatus;

  public function componentDetails()
  {
    return [
      'name'        => 'teb.simplecounsel::lang.plugin.name',
      'description' => 'teb.simplecounsel::lang.plugin.description'
    ];
  }

  public function defineProperties()
  {
    return [

    ];
  }

  public function getPropertyOptions($property)
  {
    return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    $this->prepareVars();
  }

  public function onRun()
  {
    //if (!Auth::getUser()) return redirect('/account');
    $this->addCss('/plugins/teb/simplecounsel/assets/css/simplecounsel.css');
    $this->addJs('/plugins/teb/simplecounsel/assets/js/simplecounsel.js');
    $this->prepareVars();
    $this->handleOptOutLinks();
  }

  protected function prepareVars()
  {
    $this->postPage = $this->page['postPage'] = $this->property('postPage');
    if(get('search_category')) $this->searchCategory = $this->page['search_category'] = get('search_category');
    if(get('search_string')) $this->searchString = $this->page['search_string'] = get('search_string');
    if(get('own_disease')) $this->ownDisease = $this->page['own_disease'] = get('own_disease');
    if(get('answer_status')) $this->answerStatus = $this->page['answer_status'] = get('answer_status');
  }

  protected function handleOptOutLinks()
  {

  }

  public function getCategories()
  {
    return Category::all();
  }

  public function getBestPosts()
  {
    return Post::where('is_notice', 1)->where('order_no', '<>', '0')->orderBy('order_no', 'asc')->take(3)->get();
  }

  public function getPosts()
  {

    if ($this->searchString) {
      if ($this->searchCategory) {
        if ($this->searchCategory == 'user') {
          $posts = Post::whereHas('user', function ($q) {
            $q->where('name', 'like', '%' . $this->searchString . '%');
          })->orWhere('user_name', 'like', '%' . $this->searchString . '%')->ownDisease($this->ownDisease)->orderBy('created_at', 'desc')->paginate(5);
        } else {
          $posts = Post::where($this->searchCategory, 'LIKE', '%' . $this->searchString . '%')
            ->ownDisease($this->ownDisease)->answerStatus($this->answerStatus)->orderBy('created_at', 'desc')->paginate(5);
        }
      } else {
        if($this->ownDisease) {
          $posts = Post::whereHas('user', function ($q) {
            $q->where('name', 'like', '%' . $this->searchString . '%')
              ->orWhere('user_name', 'like', '%' . $this->searchString . '%')
              ->orWhere('title', 'like', '%'.$this->searchString.'%')
              ->orWhere('content', 'like', '%'.$this->searchString.'%');
          })->where(function ($query) {
            $query->ownDisease($this->ownDisease);
          })->answerStatus($this->answerStatus)->orderBy('created_at', 'desc')->paginate(5);
        } else {
          $posts = Post::whereHas('user', function ($q) {
            $q->where('name', 'like', '%' . $this->searchString . '%');
          })->orWhere('user_name', 'like', '%' . $this->searchString . '%')
            ->orWhere('title', 'like', '%'.$this->searchString.'%')
            ->orWhere('content', 'like', '%'.$this->searchString.'%')
            ->answerStatus($this->answerStatus)
            ->orderBy('created_at', 'desc')->paginate(5);
        }

      }
    } else {
      $posts = Post::orderBy('is_notice', 'desc')->orderBy('created_at', 'desc')->ownDisease($this->ownDisease)->answerStatus($this->answerStatus)->paginate(5);
    }
    return $posts;
  }

  public function getPost($id)
  {
    $post = Post::find($id);
    return $post;
  }

  public function getPage()
  {
    $page = Request::get('page');
    if(!$page) $page = 1;

    return $page;
  }

  public function masking($string=null)
  {
    $str_len = mb_strlen($string, 'UTF-8');
    if ($string && $str_len >= 2) {
      $result = mb_substr($string, 0, 1) . '*' . mb_substr($string, 2, $str_len);
      return $result;
    }
    return $string;
  }

  public function onSaveComment()
  {
    try {
      if (!$user = Auth::getUser())
        throw new ApplicationException('로그인 해주세요.');

      if (post('content')) {
        $comment = new Comment();
        $comment->post_id = post('post_id');
        $comment->content = post('content');
        $comment->user_id = $user->id;
        $comment->save();
      }

      $post = Post::find(post('post_id'));
      $comments = Comment::where('post_id', post('post_id'))->count();
      $post->answer_count = $comments;
      $post->save();

      $this->page['post_id'] = post('post_id');
      $this->page['user'] = $user;
    } catch (Exception $ex) {
      Flash::error($ex->getMessage());
    }

  }

  public function onUpdateComment()
  {
    try {
      if (!$user = Auth::getUser())
        throw new ApplicationException('로그인 해주세요.');

      $comment = Comment::find(post('comment_id'));
      $mode = post('mode');

      if (post('mode') == 'update' && post('content')) {
        $comment->content = post('content');
        $comment->save();
      } elseif (post('mode') == 'delete') {
        $comment->delete();

        $post = Post::find($comment->post_id);
        $comments = Comment::where('post_id', post('post_id'))->count();
        $post->answer_count = $comments;
        $post->save();
      }

      $this->page['mode'] = $mode;
      $this->page['user'] = $user;
      $this->page['post_id'] = $comment->post_id;
      $this->page['comment_id'] = post('comment_id');
      $this->page['comment'] = $comment;

    } catch (Exception $ex) {
      Flash::error($ex->getMessage());
    }

  }

  public function getComments($post_id)
  {
    return Comment::where('post_id', $post_id)->orderBy('created_at', 'asc')->get();
  }


}
