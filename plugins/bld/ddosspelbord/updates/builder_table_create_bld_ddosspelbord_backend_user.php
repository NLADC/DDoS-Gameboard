<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateBldDdosspelbordBackendUser extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_backend_user', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('be_user_id')->nullable()->unsigned();
            $table->integer('party_id')->nullable()->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_backend_user');
    }
}
