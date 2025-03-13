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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id('quiz_id');

            $table->string('quiz_title')->unique()->nullable(false);

            $table->text('quiz_description')->nullable(false);

            $table->integer('quiz_difficulty')->nullable(false);

            $table->index('quiz_title');
            $table->index('quiz_description(150)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
