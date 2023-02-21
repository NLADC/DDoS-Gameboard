<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordTargets2 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->string('type', 40);
            $table->dropColumn('probes');
            $table->dropColumn('apikey');
            $table->dropColumn('protocol');
            $table->dropColumn('port');
            $table->dropColumn('query_argument');
            $table->dropColumn('interval');
            $table->dropColumn('one_off');
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_targets', function($table)
        {
            $table->dropColumn('type');
            $table->string('probes', 191)->nullable();
            $table->string('apikey', 191)->nullable();
            $table->string('protocol', 191)->nullable();
            $table->integer('port')->nullable();
            $table->string('query_argument', 191)->nullable();
            $table->integer('interval')->nullable();
            $table->boolean('one_off')->nullable();
        });
    }
}
