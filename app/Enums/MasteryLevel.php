<?php

namespace App\Enums;

enum MasteryLevel: string
{
    case NotStarted = 'not_started';
    case Learning = 'learning';
    case Memorized = 'memorized';
    case Strong = 'strong';
    case Mastered = 'mastered';
}
