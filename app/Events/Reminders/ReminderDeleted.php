<?php

namespace App\Events\Reminders;

use Illuminate\Foundation\Events\Dispatchable;

class ReminderDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly int $reminderId,
        public readonly int $userId
    ) {}
}
