<?php namespace Teb\SimpleCounsel\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Teb\SimpleCounsel\Models\Category;

/**
 * Categories Back-end Controller
 */
class Categories extends Controller
{
  public $implement = [
    'Backend.Behaviors.FormController',
    'Backend.Behaviors.ListController'
  ];

  public $formConfig = 'config_form.yaml';
  public $listConfig = 'config_list.yaml';

  public $requiredPermissions = ['teb.simplecounsel.access_simplecounsel'];

  public function __construct()
  {
    parent::__construct();

    BackendMenu::setContext('Teb.SimpleCounsel', 'simplecounsel', 'categories');

    // Add my assets
    $this->addJs('/plugins/teb/simplecounsel/assets/js/simplecounsel.js');
  }
}