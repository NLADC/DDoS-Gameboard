<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordMeasurementApi extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurement_api', function($table)
        {
            $table->string('type', 40)->default('server');
        });
    }

    public function down()
    {
        Schema::table('bld_ddosspelbord_measurement_api', function($table)
        {
            $table->dropColumn('type');
        });
    }
}
