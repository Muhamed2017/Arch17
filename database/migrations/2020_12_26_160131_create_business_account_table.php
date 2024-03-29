<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_account', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('proffession_name');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('passcode');
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
        Schema::dropIfExists('business_account');
    }
}
