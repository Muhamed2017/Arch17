<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('cover_name')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('authorable_id');
            $table->string('authorable_type');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('year')->nullable();
            $table->string('category')->nullable();   
            $table->text('types')->nullable();
            $table->boolean('display_home')->default(false);
            $table->softDeletes();
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
        Schema::dropIfExists('projects');
    }
}
