<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user)
    {
        Cache::forget('counts');
        Cache::forget('user_counts');
        Cache::tags(['users_list', 'user_searches'])->flush();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user)
    {
        Cache::forget('user_' . $user->user_id);
        Cache::tags(['users_list', 'user_searches'])->flush();
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user)
    {
        Cache::forget('counts');
        Cache::forget('user_counts');
        Cache::forget('user_' . $user->user_id);
        Cache::tags(['users_list', 'user_searches'])->flush();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
