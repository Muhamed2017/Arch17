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
            $table->string('cover')->nullable();
            $table->string('title')->nullable();
            $table->string('kind')->nullable();
            $table->longText('content')->nullable();
            $table->bigInteger('ownerable_id');
            $table->string('ownerable_type');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('year')->nullable();
            $table->string('article_type')->nullable();
            $table->string('type')->nullable();
            $table->boolean('dhome')->default(false);
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
