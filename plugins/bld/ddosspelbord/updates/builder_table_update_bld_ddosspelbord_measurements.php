<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordMeasurements extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_measurements', function($table)
        {
            $table->string('ipv', 10)->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->double('responsetime', 10, 0)->nullable();
            $table->integer('measurement_api_data_id')->nullable()->unsigned();
            $table->dropColumn('average_ttc');
            $table->dropColumn('average_rt');
            $table->dropColumn('succesrate');
            $table->dropColumn('errorcodes');

            $table->index(['timestamp','target_id'],'timestamp_target');
        });
    }

    public function down()
    {
        Schema::table('bld_ddosspelbord_measurements', function($table)
        {
            $table->dropColumn('ipv');
            $table->dropColumn('timestamp');
            $table->dropColumn('responsetime');
            $table->dropColumn('measurement_api_data_id');
            $table->integer('average_ttc')->nullable();
            $table->integer('average_rt')->nullable();
            $table->integer('succesrate')->nullable();
            $table->integer('errorcodes')->nullable();
        });
    }
}