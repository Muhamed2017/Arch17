<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->Integer('business_account_id')->unsigned();
            $table->Integer('user_id')->unsigned();
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->text('about')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('official_website')->nullable();
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
        Schema::dropIfExists('stores');
    }
}
