<?php
// app/Policies/MemorizationPlanPolicy.php

namespace App\Policies;

use App\Models\MemorizationPlan;
use App\Models\User;

class MemorizationPlanPolicy
{
    /**
     * هەموو بەکارهێنەرێک دەتوانێت پلانە چالاکەکان ببینێت
     * ئەدمین دەتوانێت هەموو پلانێک ببینێت
     */
    public function view(User $user, MemorizationPlan $plan): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $plan->status === 'active';
    }

    /**
     * تەنها ئەدمین دەتوانێت پلان دروست بکات
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * تەنها ئەدمین دەتوانێت پلان دەستکاری بکات
     */
    public function update(User $user, MemorizationPlan $plan): bool
    {
        return $user->role === 'admin';
    }

    /**
     * تەنها ئەدمین دەتوانێت پلان بسڕێتەوە
     */
    public function delete(User $user, MemorizationPlan $plan): bool
    {
        return $user->role === 'admin';
    }
}