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
            $table->string('name')->nullable();
            $table->string('user_id');
            $table->string('country');
            $table->string('email')->nullable();
            $table->string('city')->nullable()->default("");
            $table->string('phone');
            $table->string('phone_code');
            $table->text('about')->nullable()->defalut("Brand");
            $table->string('official_website')->nullable()->default("");
            $table->string('type');
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
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
