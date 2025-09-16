<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordUsers extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_users', function($table)
        {
            $table->bigInteger('party_id')->nullable()->change();
            $table->string('name', 191)->nullable()->change();
            $table->string('email', 191)->nullable()->change();
            $table->string('avatar', 191)->nullable()->change();
            $table->string('password', 191)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_users', function($table)
        {
            $table->bigInteger('party_id')->nullable(false)->change();
            $table->string('name', 191)->nullable(false)->change();
            $table->string('email', 191)->nullable(false)->change();
            $table->string('avatar', 191)->nullable(false)->change();
            $table->string('password', 191)->nullable(false)->change();
        });
    }
}
