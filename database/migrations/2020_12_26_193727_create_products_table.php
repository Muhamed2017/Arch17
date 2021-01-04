<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * kind, name , category, kind, style, places_tags,ctegory
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('store_id')->unsigned();
            $table->integer('business_account_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('kind');
            $table->string('style');
            $table->string('places_tags');
            $table->string('country');
            $table->string('city');
            $table->string('text_description');
            $table->string('category');
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
        Schema::dropIfExists('products');
    }
}
