<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('displayName')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('address')->nullable();
            $table->text('user_description')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('profession')->nullable();
            $table->boolean('is_designer')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
