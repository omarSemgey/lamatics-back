<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserQuizSubmission;
use Illuminate\Support\Facades\Cache;

class UserQuizSubmissionObserver
{
    /**
     * Handle the submission "created" event.
     */
    public function created(UserQuizSubmission $submission): void
    {
        Cache::forget('counts');
        Cache::tags([
            'submission_list',
            'submission_searches',
        ])->flush();
    }

    /**
     * Handle the submission "updated" event.
     */
    public function updated(UserQuizSubmission $submission): void
    {
        //
    }

    /**
     * Handle the submission "deleted" event.
     */
    public function deleted(UserQuizSubmission $submission): void
    {
        //
    }

    /**
     * Handle the submission "restored" event.
     */
    public function restored(UserQuizSubmission $submission): void
    {
        //
    }

    /**
     * Handle the submission "force deleted" event.
     */
    public function forceDeleted(UserQuizSubmission $submission): void
    {
        //
    }
}
