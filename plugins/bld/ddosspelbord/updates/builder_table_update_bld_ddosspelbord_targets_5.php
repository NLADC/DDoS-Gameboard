<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordTargets5 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->double('threshold_orange', 10, 0)->default(0);
            $table->double('threshold_red', 10, 0)->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->dropColumn('threshold_orange');
            $table->dropColumn('threshold_red');
        });
    }
}
