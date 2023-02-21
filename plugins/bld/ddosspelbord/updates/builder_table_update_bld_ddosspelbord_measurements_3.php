<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordMeasurements3 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurements', function($table)
        {
            $table->integer('number_of_probes')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_measurements', function($table)
        {
            $table->dropColumn('number_of_probes');
        });
    }
}
