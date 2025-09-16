<?php namespace bld\ddosspelbord\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateBldDdosspelbordBackendUserPivot extends Migration
{
    public function up()
    {
        Schema::rename('bld_ddosspelbord_backend_user', 'bld_ddosspelbord_backend_user_pivot');
    }
    
    public function down()
    {
        Schema::rename('bld_ddosspelbord_backend_user_pivot', 'bld_ddosspelbord_backend_user');
    }
}
