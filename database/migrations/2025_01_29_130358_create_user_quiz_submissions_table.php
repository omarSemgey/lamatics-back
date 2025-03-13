<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_quiz_submissions', function (Blueprint $table) {
            $table->id('user_submission_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('quiz_id');
            $table->integer('score');

            $table->json('answers')->comment('Structure: {
                question_id: selected_answer_id
            }');

            $table->unique(['user_id', 'quiz_id']); 
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users');
            $table->foreign('quiz_id')->references('quiz_id')->on('quizzes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quiz_submissions');
    }
};
