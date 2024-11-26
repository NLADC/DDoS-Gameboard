<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordMeasurementNodePivot extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurement_node_pivot', function($table)
        {
            $table->renameColumn('measurement_node_id', 'measurement_nodelist_id');
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_measurement_node_pivot', function($table)
        {
            $table->renameColumn('measurement_nodelist_id', 'measurement_node_id');
        });
    }
}
