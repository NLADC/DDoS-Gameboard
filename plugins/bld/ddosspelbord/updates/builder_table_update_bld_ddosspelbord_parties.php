<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordParties extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_parties', function($table)
        {
            $table->boolean('is_active')->default(1);
            $table->string('logo', 191)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_parties', function($table)
        {
            $table->dropColumn('is_active');
            $table->string('logo', 191)->default(null)->change();
        });
    }
}
