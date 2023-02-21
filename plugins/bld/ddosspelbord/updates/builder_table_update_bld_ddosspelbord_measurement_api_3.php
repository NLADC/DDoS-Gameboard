<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordMeasurementApi3 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurement_api', function($table)
        {
            $table->string('billingemail', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_measurement_api', function($table)
        {
            $table->dropColumn('billingemail');
        });
    }
}
