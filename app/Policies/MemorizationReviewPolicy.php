<?php
// app/Policies/MemorizationReviewPolicy.php

namespace App\Policies;

use App\Models\MemorizationReview;
use App\Models\User;

class MemorizationReviewPolicy
{
    public function view(User $user, MemorizationReview $review): bool
    {
        return $user->id === $review->user_id || $user->role === 'admin';
    }

    public function update(User $user, MemorizationReview $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function delete(User $user, MemorizationReview $review): bool
    {
        return $user->id === $review->user_id || $user->role === 'admin';
    }
}