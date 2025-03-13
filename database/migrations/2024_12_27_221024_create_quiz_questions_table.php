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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id('quiz_question_id');

            
            $table->text('question_description')->nullable(false);

            $table->text('question_image')->nullable();

            $table->unsignedBigInteger('quiz_id'); 
            $table->foreign('quiz_id')->references('quiz_id')->on('quizzes')->onDelete('cascade');

            $table->timestamps();
        });    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
