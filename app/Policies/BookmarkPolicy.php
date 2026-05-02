<?php


namespace App\Policies;

use App\Models\Bookmark;
use App\Models\User;

class BookmarkPolicy
{
    public function view(User $user, Bookmark $bookmark): bool
    {
        return $user->id === $bookmark->user_id || $user->role === 'admin';
    }

    public function update(User $user, Bookmark $bookmark): bool
    {
        return $user->id === $bookmark->user_id;
    }

    public function delete(User $user, Bookmark $bookmark): bool
    {
        return $user->id === $bookmark->user_id || $user->role === 'admin';
    }
}