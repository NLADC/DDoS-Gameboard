<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordActions extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_actions', function($table)
        {
            $table->string('name', 191)->nullable()->change();
            $table->string('description', 191)->nullable()->change();
            $table->string('tag', 191)->nullable()->change();
            $table->dateTime('start')->nullable()->change();
            $table->integer('length')->nullable()->change();
            $table->integer('delay')->nullable()->change();
            $table->integer('extension')->nullable()->change();
            $table->boolean('has_issues')->nullable()->change();
            $table->boolean('is_cancelled')->nullable()->change();
            $table->string('highlight', 6)->nullable()->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_actions', function($table)
        {
            $table->string('name', 191)->nullable(false)->change();
            $table->string('description', 191)->nullable(false)->change();
            $table->string('tag', 191)->nullable(false)->change();
            $table->dateTime('start')->nullable(false)->change();
            $table->integer('length')->nullable(false)->change();
            $table->integer('delay')->nullable(false)->change();
            $table->integer('extension')->nullable(false)->change();
            $table->boolean('has_issues')->nullable(false)->change();
            $table->boolean('is_cancelled')->nullable(false)->change();
            $table->string('highlight', 6)->nullable(false)->default(null)->change();
        });
    }
}
