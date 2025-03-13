<?php

namespace App\Observers;

use App\Models\Quiz;
use Illuminate\Support\Facades\Cache;

class QuizObserver
{
    /**
     * Handle the quiz "created" event.
     */
    public function created(Quiz $quiz): void
    {
        Cache::forget('counts');
        Cache::tags(['quizzes_list', 'quiz_searches'])->flush();
    }

    /**
     * Handle the quiz "updated" event.
     */
    public function updated(Quiz $quiz): void
    {
        Cache::forget('quiz_' . $quiz->quiz_id);
        Cache::tags(['quizzes_list', 'quiz_searches'])->flush();
    }

    /**
     * Handle the quiz "deleted" event.
     */
    public function deleted(Quiz $quiz): void
    {
        Cache::forget('counts');
        Cache::forget('quiz_' . $quiz->quiz_id);
        Cache::tags(['quizzes_list', 'quiz_searches'])->flush();
    }

    /**
     * Handle the quiz "restored" event.
     */
    public function restored(Quiz $quiz): void
    {
        //
    }

    /**
     * Handle the quiz "force deleted" event.
     */
    public function forceDeleted(Quiz $quiz): void
    {
        //
    }
}
