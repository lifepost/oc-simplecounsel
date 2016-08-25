<?php namespace Teb\SimpleCounsel\Models;

use Model;

/**
 * Comment Model
 */
class Comment extends Model
{

  public $table = 'teb_simplecounsel_comments';

  public $belongsTo = [
    'post' => ['Teb\SimpleCounsel\Models\Post'],
    'user' => ['RainLab\User\Models\User']
  ];

}