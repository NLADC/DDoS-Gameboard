<?php namespace bld\ddosspelbord\Updates;

use Winter\Storm\Database\Updates\Migration;
use Schema;

class InitDatabase extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_actions', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('name', 191)->nullable();
            $table->string('description', 191)->nullable();
            $table->string('tag', 191)->nullable();
            $table->dateTime('start')->nullable();
            $table->unsignedInteger('length')->default(0)->nullable();
            $table->integer('delay')->default(0)->nullable();
            $table->unsignedInteger('extension')->default(0)->nullable();
            $table->tinyInteger('has_issues')->default(0)->nullable();
            $table->tinyInteger('is_cancelled')->default(0)->nullable();
            $table->string('highlight', 6)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bld_ddosspelbord_attacks', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 999)->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status', 191)->nullable();
            $table->timestamps();
            $table->dateTime('timestamp')->nullable();
            $table->softDeletes();
        });

        Schema::create('bld_ddosspelbord_logs', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('log')->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bld_ddosspelbord_parties', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 191)->nullable();
            $table->string('logo', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bld_ddosspelbord_roles', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 191)->nullable();
            $table->string('display_name', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('name', 'roles_name_unique');
        });

        Schema::create('bld_ddosspelbord_transactions', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('hash', 32)->nullable();
            $table->string('type', 191)->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bld_ddosspelbord_users', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->unsignedInteger('role_id')->nullable()->index('bld_users_role_id_foreign');
            $table->bigInteger('party_id')->unsigned()->nullable();
            $table->string('name', 191)->nullable();
            $table->string('email', 191)->unique()->nullable();
            $table->string('avatar', 191)->default('users/default.png')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 191)->nullable();
            $table->string('api_token', 80)->nullable()->unique();
            $table->string('remember_token', 100)->nullable();
            $table->text('settings')->nullable();
            $table->timestamps();
            $table->datetime('heartbeat')->nullable();
            $table->softDeletes();
        });

    }

    public function down()
    {
        Schema::dropIfExists('bld_ddosspelbord_actions');
        Schema::dropIfExists('bld_ddosspelbord_attacks');
        Schema::dropIfExists('bld_ddosspelbord_logs');
        Schema::dropIfExists('bld_ddosspelbord_parties');
        Schema::dropIfExists('bld_ddosspelbord_roles');
        Schema::dropIfExists('bld_ddosspelbord_transactions');
        Schema::dropIfExists('bld_ddosspelbord_users');
    }
};
