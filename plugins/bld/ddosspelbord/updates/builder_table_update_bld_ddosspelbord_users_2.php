<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordUsers2 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_users', function($table)
        {
            $table->string('organization')->nullable();
            $table->string('department')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('bhv')->nullable()->default(false);
            $table->boolean('friday_present')->nullable()->default(false);
            $table->boolean('saterday_present')->nullable()->default(false);
            $table->boolean('friday_diner')->nullable()->default(false);
            $table->boolean('saterday_breakfast')->nullable()->default(false);
            $table->text('dietary')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_users', function($table)
        {
            $table->dropColumn('organization');
            $table->dropColumn('department');
            $table->dropColumn('phone');
            $table->dropColumn('bhv');
            $table->dropColumn('friday_present');
            $table->dropColumn('saterday_present');
            $table->dropColumn('friday_diner');
            $table->dropColumn('saterday_breakfast');
            $table->dropColumn('dietary');
        });
    }
}
