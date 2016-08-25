<?php namespace Teb\SimpleCounsel\Components;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Lang;
use Auth;
use Mail;
use Event;
use Flash;
use Input;
use Request;
use Redirect;
use File;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Exception;
use Teb\SimpleCounsel\Models\Category;
use Teb\SimpleCounsel\Models\Post;
use Teb\SimpleCounsel\Models\Comment;


class SimpleCounselPost extends ComponentBase
{

  public $postPage;
  public $postId;
  public $post;
  public $checkPassword;

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
    $this->addCss('/plugins/teb/simplecounsel/assets/css/simplecounsel.css');
    $this->addJs('/plugins/teb/simplecounsel/assets/js/simplecounsel.js');
    $this->prepareVars();
    $this->handleOptOutLinks();
    if($this->postId) {
      $this->post = $this->page['post'] = Post::find($this->postId);

      if (@Auth::getUser()->id != $this->post->user_id) {
        return redirect('/simplecounsel/create');
      }
    }
  }

  protected function prepareVars()
  {
    $this->postPage = $this->page['postPage'] = $this->property('postPage');
    $this->postId = $this->page['postId'] = $this->property('postId');
    //$this->checkPassword = $this->page['checkPassword'] = $this->property('checkPassword');
  }

  protected function handleOptOutLinks()
  {

  }

  public function getCategories()
  {
    return Category::all();
  }

  public function onCreate()
  {
    $data = post();
    if (Auth::getUser()) {
      $rules = [
        'category' => 'required',
        'title'         => 'required',
        'content'      => 'required'
      ];
    } else {
      $rules = [
        'user_name' => 'required',
        'user_email' => 'required|email',
        'user_password' => 'required',
        'category' => 'required',
        'title'         => 'required',
        'content'      => 'required'
      ];
    }

    $validation = Validator::make($data, $rules);
    if ($validation->fails()) {
      Flash::error($validation->messages()->first());
      return;
    }

    try {
      $user = Auth::getUser();

      $post = new Post();
      if ($user) {
        $post->user_id = $user->id;
      } else {
        $post->user_name = post('user_name');
        $post->user_email = post('user_email');
        $post->user_password = bcrypt(post('user_password'));
      }
      $post->title = post('title');
      $post->content = post('content');
      $post->category_id = post('category');

      $post->save();

      return redirect('/simplecounsel');
    }
    catch (Exception $ex) {
      Flash::error($ex->getMessage());
    }
  }

  public function onUpdate()
  {
    $data = post();
    $rules = [
      'title'         => 'required',
      'content'      => 'required',
      'category' => 'required'
    ];

    $validation = Validator::make($data, $rules);
    if ($validation->fails()) {
      Flash::error($validation->messages()->first());
      $this->page['checkPassword'] = 1;
      return;
    }

    try {
      $post = Post::find($this->property('postId'));

      $post->title = post('title');
      $post->content = post('content');
      $post->category_id = post('category');

      $post->save();

      return redirect('/simplecounsel');
    }
    catch (Exception $ex) {
      Flash::error($ex->getMessage());
    }
  }

  public function onDelete()
  {
    $post_id = $this->property('postId');
    $post = Post::find($post_id);

    if (! $post->delete()) {
      throw new Exception('Post delete error ...');
    }

    $comments = Comment::where('post_id', $post_id);
    if ($comments->count() > 0) {
      if (! $comments->delete()) {
        throw new Exception('Comments delete error ...');
      }
    }

    return redirect('/simplecounsel');
  }

  public function onCheckPassword()
  {
    $data = post();
    $rules = [
      'user_password'         => 'required'
    ];

    $validation = Validator::make($data, $rules);
    if ($validation->fails()) {
      Flash::error($validation->messages()->first());
      return;
    }

    try {
      $post = Post::find($this->property('postId'));
      if (!Hash::check(post('user_password'), $post->user_password)) {
        throw new ApplicationException('비밀번호가 일치하지 않습니다.');
        return redirect(URL::previous());
      }
      $this->page['checkPassword'] = 1;
    }
    catch (Exception $ex) {
      Flash::error($ex->getMessage());
    }
  }
}
