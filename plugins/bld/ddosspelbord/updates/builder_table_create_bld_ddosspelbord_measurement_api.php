<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateBldDdosspelbordMeasurementApi extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_measurement_api', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name', 255);
            $table->text('description');
            $table->string('modulename', 255)->default('');
            $table->text('configjson');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_measurement_api');
    }
}
