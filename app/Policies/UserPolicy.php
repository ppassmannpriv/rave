<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function update(User $user, User $user_model): Response
    {
        dd(1);
        return $user->id === $user_model->id ? Response::allow() : Response::deny();
    }

    public function updateProfile(User $user, User $user_model): Response
    {
        return $user->id === $user_model->id ? Response::allow() : Response::deny();
    }
}
