<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->string("code")->nullable();
            $table->text("covers")->nullable();
            $table->bigInteger("price")->nullable();
            $table->bigInteger("offer_price")->nullable();
            $table->string("material_name")->nullable();
            $table->string("material_image")->nullable();
            $table->string("size")->nullable();
            $table->integer("quantity")->nullable();
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
        Schema::dropIfExists('options');
    }
}
