<?php namespace Teb\SimpleCounsel;

use System\Classes\PluginBase;
use BackendAuth;
use Backend;
use Config;
use Event;
use Cache;
use Request;
use App;
use Flash;

/**
 * SimpleCounsel Plugin Information File
 * Plugin icon is used with Creative Commons (CC BY 4.0) Licence
 * Icon author: http://pixelkit.com/
 */
class Plugin extends PluginBase
{

  /**
   * Returns information about this plugin.
   *
   * @return array
   */
  public function pluginDetails()
  {
    return [
      'name' => '간편상담',
      'description' => '간편상담 플러그인',
      'author' => 'jsy',
      'icon' => 'icon-cubes'
    ];
  }

  public function registerNavigation()
  {
    return [
      'afterstory' => [
        'label'       => 'SimpleCounsel',
        'url'         => Backend::url('teb/simplecounsel/posts'),
        'icon'        => 'icon-list-ol',
        'permissions' => ['teb.simplecounsel.*'],
        'order'       => 500
      ]
    ];
  }

  public function registerPermissions()
  {
    return array('teb.simplecounsel.access_simplecounsel' => ['label' => 'Simplecounsel menu', 'tab' => 'MenuManager']);
  }

  public function registerComponents()
  {
    return [
      'Teb\SimpleCounsel\Components\SimpleCounsel'       => 'simpleCounsel',
      'Teb\SimpleCounsel\Components\SimpleCounselPost'       => 'simpleCounselPost'
    ];
  }

  public function boot() {

  }
}
