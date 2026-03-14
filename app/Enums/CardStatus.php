<?php

namespace App\Enums;

enum CardStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Repaired = 'repaired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Repaired => 'Repaired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::InProgress => 'blue',
            self::Repaired => 'emerald',
        };
    }
}
