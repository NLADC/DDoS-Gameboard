<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordActionPlans extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_action_plans', function($table)
        {
            $table->renameColumn('data', 'plandata');
            $table->dropColumn('party_id');
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_action_plans', function($table)
        {
            $table->renameColumn('plandata', 'data');
            $table->integer('party_id')->nullable()->unsigned();
        });
    }
}
