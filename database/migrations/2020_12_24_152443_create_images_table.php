<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
         $table->id();

            $table->integer('imageable_id');
            $table->string('imageable_type');
            
            $table->string('img_public_id')->nullable();
            $table->string('thumb_public_id')->nullable();
            
            $table->string('img_url');
            $table->string('thumb_url');

            $table->string('img_width')->nullable();
            $table->string('img_height')->nullable();
            $table->string('thumb_width')->nullable();
            $table->string('thumb_height')->nullable();

            $table->string('img_bytes')->nullable();
            $table->string('thumb_bytes')->nullable();

            $table->string('format')->nullable();
            $table->string('original_filename')->nullable();

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
        Schema::dropIfExists('images');
    }
}
