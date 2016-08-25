<?php namespace Teb\SimpleCounsel\Models;

use Illuminate\Support\Facades\Log;
use Model;

/**
 * Post Model
 */
class Post extends Model
{
  public $table = 'teb_simplecounsel_posts';

  public $belongsTo = [
    'category' => ['Teb\SimpleCounsel\Models\Category'],
    'user' => ['RainLab\User\Models\User']
  ];

  public $rules = [
    'title'         => 'required|max:2',
    'content'      => 'required',
    'category_id' => 'required'
  ];

  function scopeOwnDisease($query, $string = null)
  {
    if ($string) {
      $query->where('category_id', $string);
    }
    return $query;
  }

  function scopeAnswerStatus($query, $string = null)
  {
    if ($string && $string == '1') {
      $query->where('answer_count', '=', 0);
    } else if ($string && $string == '2') {
      $query->where('answer_count', '>', 0);
    }
    return $query;
  }

  public function scopeFilterByCategory($query, $filter)
  {
    return $query->whereIn('category_id', $filter);
  }
}