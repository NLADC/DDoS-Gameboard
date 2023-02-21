<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateBldDdosspelbordMeasurementApiData extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_measurement_api_data', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('measurement_api_id')->unsigned();
            $table->text('measurement_datajson');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_measurement_api_data');
    }
}
