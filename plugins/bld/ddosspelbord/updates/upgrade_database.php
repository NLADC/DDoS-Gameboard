<?php namespace bld\ddosspelbord\Updates;

use Winter\Storm\Database\Updates\Migration;
use Schema;

class UpgradeDatabase extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_targets', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 191)->nullable();
            $table->string('target', 191)->nullable();
            $table->string('ipv', 191)->nullable();
            $table->unsignedBigInteger('measurement_api_id')->nullable();
            $table->string('type', 40)->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->boolean('enabled')->default(false);
            $table->double('threshold_orange')->default(0)->nullable();
            $table->double('threshold_red')->default(0)->nullable();
            $table->string('groups', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bld_ddosspelbord_measurements', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('ipv', 10)->nullable();
            $table->datetime('timestamp')->nullable();
            $table->double('responsetime')->nullable();
            $table->unsignedBigInteger('measurement_api_data_id')->nullable();
            $table->integer('number_of_probes')->default(0)->nullable();;
            $table->timestamps();

            $table->index(['timestamp', 'target_id']);
        });

        Schema::create('bld_ddosspelbord_target_groups', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->integer('sortnr')->default(1)->nullable();
            $table->double('graphresponsetimeclipvalue')->default(200)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sortnr']);
        });

        Schema::create('bld_ddosspelbord_measurement_api', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('modulename', 255)->nullable();
            $table->text('configjson')->nullable();
            $table->string('type', 40)->default('website')->nullable();
            $table->string('apikey', 255)->nullable();
            $table->string('billingemail', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bld_ddosspelbord_measurement_api_data', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('measurement_api_id')->unsigned()->nullable();;
            $table->integer('target_id')->unsigned()->nullable();;
            $table->text('datajson')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_targets');
        Schema::dropIfExists('bld_ddosspelbord_measurements');
        Schema::dropIfExists('bld_ddosspelbord_target_groups');
        Schema::dropIfExists('bld_ddosspelbord_measurement_api');
        Schema::dropIfExists('bld_ddosspelbord_measurement_api_data');
    }
};