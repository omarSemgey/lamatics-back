<?php

namespace App\Providers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\UserQuizSubmission;
use App\Observers\QuizObserver;
use App\Observers\UserObserver;
use App\Observers\UserQuizSubmissionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Quiz::observe(QuizObserver::class);
        UserQuizSubmission::observe(UserQuizSubmissionObserver::class);
    }
}
