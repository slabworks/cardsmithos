<?php

namespace App\Enums;

enum CommunicationMethod: string
{
    case Email = 'email';
    case Phone = 'phone';
    case Discord = 'discord';
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case Twitter = 'twitter';
    case InPerson = 'in_person';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Phone => 'Phone',
            self::Discord => 'Discord',
            self::Instagram => 'Instagram',
            self::Facebook => 'Facebook',
            self::Twitter => 'Twitter',
            self::InPerson => 'In Person',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Email => 'blue',
            self::Phone => 'emerald',
            self::Discord => 'violet',
            self::Instagram => 'pink',
            self::Facebook => 'sky',
            self::Twitter => 'cyan',
            self::InPerson => 'amber',
            self::Other => 'slate',
        };
    }
}
