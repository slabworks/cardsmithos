<?php

namespace App\Enums;

enum MessageSenderType: string
{
    case User = 'user';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Store Owner',
            self::Customer => 'Customer',
        };
    }
}
