<?php namespace bld\ddosspelbord\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class builderTableCreateBldDdosspelbordMeasurements extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_measurements', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('target_id')->unsigned()->nullable();
            $table->integer('average_ttc')->nullable();
            $table->integer('average_rt')->nullable();
            $table->integer('succesrate')->nullable();
            $table->integer('errorcodes')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_measurements');
    }
}