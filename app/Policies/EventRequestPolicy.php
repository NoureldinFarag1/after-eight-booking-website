<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EventRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventRequestPolicy
{
    use HandlesAuthorization;

    public function view(User $user, EventRequest $eventRequest)
    {
        return $user->id === $eventRequest->user_id || $user->is_admin ?? false;
    }

    public function admin(User $user)
    {
        return $user->is_admin ?? false;
    }
}
