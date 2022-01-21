<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('covers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("option_id")->unsigned()->nullable();
            $table->string('original');
            $table->string('cropped')->nullable();
            $table->double('width')->nullable();
            $table->double('height')->nullable();
            $table->text('crop_data')->nullable();
            $table->string('thumb')->nullable();
            $table->double('size')->nullable();
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
        Schema::dropIfExists('covers');
    }
}
