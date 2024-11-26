<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordMeasurementTypes extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurement_types', function($table)
        {
            $table->integer('nodelist_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_measurement_types', function($table)
        {
            $table->dropColumn('nodelist_id');
        });
    }
}
