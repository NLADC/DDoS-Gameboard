<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateBldDdosspelbordActionPlans extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_action_plans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('user_last_edited_id')->nullable()->unsigned();
            $table->text('data')->nullable();
            $table->integer('party_id')->nullable()->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_action_plans');
    }
}
