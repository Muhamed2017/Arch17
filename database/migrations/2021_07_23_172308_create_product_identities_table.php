<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductIdentitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_identities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('store_id')->unsigned();
            $table->string('name')->nullable();
            $table->string('kind')->nullable();
            $table->string('shape')->nullable();
            $table->string('seats')->nullable();
            $table->string('material')->nullable();
            $table->string('base')->nullable();
            $table->string('type')->nullable();
            $table->string('product_file_kind')->nullable();
            $table->string('is_for_kids')->default('no');
            $table->string('is_outdoor')->default('no');
            $table->string('style')->nullable();
            $table->string('places_tags')->nullable();
            $table->string('colorTempratures')->nullable();
            $table->string('bulbTypes')->nullable();
            $table->string('applied_on')->nullable();
            $table->string('installations')->nullable();
            $table->string('lighting_types')->nullable();
            $table->string('country')->nullable();
            $table->string('category')->nullable();
            $table->string('preview_cover')->nullable();
            $table->string('preview_price')->nullable();
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
        Schema::dropIfExists('product_identities');
    }
}
