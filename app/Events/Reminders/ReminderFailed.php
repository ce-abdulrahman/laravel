<?php

namespace App\Events\Reminders;

use App\Models\ReminderLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ReminderLog $log,
        public readonly string $reason = ''
    ) {}
}
