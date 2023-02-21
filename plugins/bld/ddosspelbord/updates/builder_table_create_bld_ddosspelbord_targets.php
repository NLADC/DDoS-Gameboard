<?php namespace bld\ddosspelbord\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class builderTableCreateBldDdosspelbordTargets extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_targets', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name')->nullable();
            $table->string('target')->nullable();
            $table->string('probes')->nullable();
            $table->string('apikey')->nullable();
            $table->string('protocol')->nullable();
            $table->integer('port')->nullable();
            $table->string('query_argument')->nullable();
            $table->integer('interval')->nullable();
            $table->string('ipv')->nullable();
            $table->boolean('one_off')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_targets');
    }
}