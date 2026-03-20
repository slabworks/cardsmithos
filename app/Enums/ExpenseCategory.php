<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Supplies = 'supplies';
    case Shipping = 'shipping';
    case Equipment = 'equipment';
    case Software = 'software';
    case Marketing = 'marketing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Supplies => 'Supplies',
            self::Shipping => 'Shipping',
            self::Equipment => 'Equipment',
            self::Software => 'Software',
            self::Marketing => 'Marketing',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Supplies => 'emerald',
            self::Shipping => 'blue',
            self::Equipment => 'violet',
            self::Software => 'amber',
            self::Marketing => 'sky',
            self::Other => 'slate',
        };
    }
}
