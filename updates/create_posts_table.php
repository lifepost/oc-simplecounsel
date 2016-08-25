<?php namespace Teb\SimpleCounsel\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePostsTable extends Migration
{

  public function up()
  {
    Schema::create('teb_simplecounsel_posts', function($table)
    {
      $table->engine = 'InnoDB';
      $table->increments('id');
      $table->integer('category_id')->unsigned();
      $table->integer('user_id')->unsigned();
      $table->string('title');
      $table->text('content');
      $table->string('user_name')->nullable();
      $table->string('user_email')->nullable();
      $table->string('user_password')->nullable();
      $table->integer('order_no');
      $table->boolean('is_notice');
      $table->integer('answer_count');
      $table->dateTime('created_at');
      $table->dateTime('updated_at');
    });
  }

  public function down()
  {
    Schema::dropIfExists('teb_simplecounsel_posts');
  }

}