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
        Schema::create('quiz_question_answers', function (Blueprint $table) {
            $table->id('question_answer_id');

            $table->text('question_answer_text')->nullable(false);

            $table->boolean('correct_answer')->default(false);

            $table->unsignedBigInteger('question_id'); 
            $table->foreign('question_id')->references('quiz_question_id')->on('quiz_questions')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_question_answers');
    }
};
