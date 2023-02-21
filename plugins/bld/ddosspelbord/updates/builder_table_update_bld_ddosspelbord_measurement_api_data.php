<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordMeasurementApiData extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurement_api_data', function($table)
        {
            $table->integer('target_id')->unsigned();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->renameColumn('measurement_datajson', 'datajson');
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_measurement_api_data', function($table)
        {
            $table->dropColumn('target_id');
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');
            $table->renameColumn('datajson', 'measurement_datajson');
        });
    }
}
