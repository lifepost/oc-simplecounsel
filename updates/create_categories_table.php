<?php namespace Teb\SimpleCounsel\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesTable extends Migration
{

  public function up()
  {
    Schema::create('teb_simplecounsel_categories', function($table)
    {
      $table->engine = 'InnoDB';
      $table->increments('id');
      $table->string('name');
      $table->integer('order_no');
      $table->dateTime('created_at');
      $table->dateTime('updated_at');
    });
  }

  public function down()
  {
    Schema::dropIfExists('teb_simplecounsel_categories');
  }

}