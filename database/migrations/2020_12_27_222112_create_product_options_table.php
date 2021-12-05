<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // public function up()
    // {
    //     Schema::create('product_options', function (Blueprint $table) {
    //         $table->id();
    //         // $table->integer('product_id')->unsigned();
    //         // $table->string('material_name')->nullable();
    //         // $table->string('material_image')->nullable();
    //         // $table->text('cover')->nullable();
    //         // $table->string('size')->nullable();
    //         // $table->string('price')->nullable();
    //         // $table->string('offer_price')->nullable();
    //         // $table->string('quantity')->nullable();
    //         // $table->string('code')->nullable();
    //         // $table->timestamps();
    //     });
    // }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_options');
    }
}
