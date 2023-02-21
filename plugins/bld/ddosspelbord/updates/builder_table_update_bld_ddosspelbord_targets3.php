<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordTargets3 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->string('groups', 255);
        });
    }

    public function down()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->dropColumn('groups');
        });
    }
}