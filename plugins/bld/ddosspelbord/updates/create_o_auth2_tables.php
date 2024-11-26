<?php namespace bld\ddosspelbord\Updates;

use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateOAuth2Tables extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()   {
        Schema::create('oauth_access_tokens', function ($table) {
            $table->engine = 'InnoDB';
            $table->string('id', 100)->primary();
            $table->integer('user_id')->index()->nullable();
            $table->integer('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamp('last_used')->nullable();
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_auth_codes', function ($table) {
            $table->engine = 'InnoDB';
            $table->string('id', 100)->primary();
            $table->integer('user_id');
            $table->integer('client_id');
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_clients', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->index()->nullable();
            $table->string('name');
            $table->string('secret', 100);
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_personal_access_clients', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('client_id')->index();
            $table->timestamps();
        });

        Schema::create('oauth_refresh_tokens', function ($table) {
            $table->engine = 'InnoDB';
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });
        Schema::create('backend_users_password_resets', function ($table) {
            $table->engine = 'InnoDB';
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oauth_access_tokens');
        Schema::drop('oauth_auth_codes');
        Schema::drop('oauth_clients');
        Schema::drop('oauth_personal_access_clients');
        Schema::drop('oauth_refresh_tokens');
        Schema::dropIfExists('backend_users_password_resets');
    }
};