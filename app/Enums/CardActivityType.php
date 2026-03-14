<?php

namespace App\Enums;

enum CardActivityType: string
{
    case Milestone = 'milestone';
    case Activity = 'activity';

    public function label(): string
    {
        return match ($this) {
            self::Milestone => 'Milestone',
            self::Activity => 'Activity',
        };
    }
}
