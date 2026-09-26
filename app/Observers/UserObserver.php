<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user): void
    {
        Log::info('UserObserver created fired', ['user_id' => $user->id]);
    }

    public function updated(User $user): void
    {
        // DO NOTHING
    }

    public function saving(User $user): void
    {
        // DO NOTHING
    }

    public function saved(User $user): void
    {
        // DO NOTHING
    }
}
