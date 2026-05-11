<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Complete = 'complete';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Complete => 'Complete',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::InProgress => 'blue',
            self::Complete => 'emerald',
            self::Cancelled => 'gray',
        };
    }
}
