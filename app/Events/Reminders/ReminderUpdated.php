<?php

namespace App\Events\Reminders;

use App\Models\UserReminder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly UserReminder $reminder) {}
}
