<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case ColdLead = 'cold_lead';
    case WarmLead = 'warm_lead';
    case HotLead = 'hot_lead';
    case InProgress = 'in_progress';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ColdLead => 'Cold Lead',
            self::WarmLead => 'Warm Lead',
            self::HotLead => 'Hot Lead',
            self::InProgress => 'In Progress',
            self::Inactive => 'Inactive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ColdLead => 'slate',
            self::WarmLead => 'sky',
            self::HotLead => 'amber',
            self::InProgress => 'blue',
            self::Inactive => 'gray',
        };
    }
}
