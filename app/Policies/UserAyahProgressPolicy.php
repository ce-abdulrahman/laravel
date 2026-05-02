<?php

namespace App\Policies;

use App\Models\UserAyahProgress;
use App\Models\User;

class UserAyahProgressPolicy
{
    public function view(User $user, UserAyahProgress $progress): bool
    {
        return $user->id === $progress->user_id || $user->role === 'admin';
    }

    public function update(User $user, UserAyahProgress $progress): bool
    {
        return $user->id === $progress->user_id || $user->role === 'admin';
    }

    public function delete(User $user, UserAyahProgress $progress): bool
    {
        return $user->id === $progress->user_id || $user->role === 'admin';
    }
}