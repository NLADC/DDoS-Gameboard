<?php
/*
 * Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
 *
 * This file is part of the DDoS gameboard.
 *
 * DDoS gameboard is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * DDoS gameboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see @link https://www.gnu.org/licenses/.
 *
 */

namespace Bld\Ddosspelbord\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class create_database extends Migration
{
    public function up()
    {
        Schema::create('bld_ddosspelbord_actions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('party_id')->nullable();
            $table->string('name');
            $table->string('description');
            $table->string('tag');
            $table->dateTime('start');
            $table->integer('length')->default(0);
            $table->integer('delay')->default(0);
            $table->integer('extension')->default(0);
            $table->boolean('has_issues')->default(0);
            $table->boolean('is_cancelled')->default(0);
            $table->string('highlight', 6)->default('');
        });

        Schema::create('bld_ddosspelbord_attacks', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name')->nullable();
            $table->unsignedInteger('party_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('timestamp')->nullable();
        });

        Schema::create('bld_ddosspelbord_logs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->text('log')->nullable();
            $table->dateTime('timestamp')->nullable();
        });

        Schema::create('bld_ddosspelbord_parties', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name');
            $table->string('logo');
        });

        Schema::create('bld_ddosspelbord_roles', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name')->unique('bld_roles_name_unique');
            $table->string('display_name');
        });

        Schema::create('bld_ddosspelbord_transactions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('hash', 40);
            $table->string('type');
            $table->text('data');
        });

        Schema::create('bld_ddosspelbord_users', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('role_id')->nullable()->index('bld_users_role_id_foreign');
            $table->unsignedInteger('party_id');
            $table->string('name');
            $table->string('email')->unique('bld_users_email_unique');
            $table->string('avatar')->nullable()->default('users/default.png');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('api_token', 80)->nullable()->unique('bld_users_api_token_unique');
            $table->text('settings')->nullable();
            $table->dateTime('heartbeat')->nullable();
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
}
