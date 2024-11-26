<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateBldDdosspelbordMeasurementNodePivot extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_measurement_node_pivot', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('measurement_type_id')->nullable()->unsigned();
            $table->integer('measurement_node_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_measurement_node_pivot');
    }
}
