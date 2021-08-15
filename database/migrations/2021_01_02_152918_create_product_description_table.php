<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_description', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->unsigned();
            $table->longText('overview_content')->nullable();
            $table->longText('mat_desc_content')->nullable();
            $table->longText('size_content')->nullable();
            $table->text('desc_overview_img')->nullable();
            $table->text('desc_mat_desc_img')->nullable();
            $table->text('desc_dimension_img')->nullable();
            $table->text('desc_gallery_files')->nullable();
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
        Schema::dropIfExists('product_description');
    }
}
