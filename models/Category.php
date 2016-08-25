<?php namespace Teb\SimpleCounsel\Models;

use Model;

/**
 * Category Model
 */
class Category extends Model
{

  public $table = 'teb_simplecounsel_categories';

  public $hasMany = [
    'posts' => 'Teb\SimpleCounsel\Models\Post'
  ];
}