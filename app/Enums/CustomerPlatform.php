<?php

namespace App\Enums;

enum CustomerPlatform: string
{
    case Email = 'email';
    case Instagram = 'instagram';
    case Website = 'website';
    case InPerson = 'in_person';
    case TikTok = 'tiktok';
    case YouTube = 'youtube';
    case Facebook = 'facebook';
    case XTwitter = 'x_twitter';
    case Referral = 'referral';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Instagram => 'Instagram',
            self::Website => 'Website',
            self::InPerson => 'In Person',
            self::TikTok => 'TikTok',
            self::YouTube => 'YouTube',
            self::Facebook => 'Facebook',
            self::XTwitter => 'X / Twitter',
            self::Referral => 'Referral',
            self::Other => 'Other',
        };
    }
}
