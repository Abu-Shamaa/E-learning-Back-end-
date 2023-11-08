<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('level_id');
            $table->string('title');
            $table->text('q_content')->nullable();
            $table->string('question_type');
            $table->string('answer');
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses');
            $table->foreign('topic_id')->references('id')->on('topics');
            $table->foreign('level_id')->references('id')->on('levels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
};
