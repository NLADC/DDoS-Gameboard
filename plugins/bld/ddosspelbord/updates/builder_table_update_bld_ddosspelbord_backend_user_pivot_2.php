<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordBackendUserPivot2 extends Migration
{
    public function up()
    {
        Schema::table('bld_ddosspelbord_backend_user_pivot', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('bld_ddosspelbord_backend_user_pivot', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
