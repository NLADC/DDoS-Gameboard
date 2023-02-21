<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateBldDdosspelbordTargetGroups extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_target_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name', 255);
            $table->integer('sortnr')->default(1);
            $table->double('graphresponsetimeclipvalue', 10, 0)->default(200);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_target_groups');
    }
}
