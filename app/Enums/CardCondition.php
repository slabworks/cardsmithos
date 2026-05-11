<?php

namespace App\Enums;

enum CardCondition: string
{
    case Damaged = 'damaged';
    case HeavilyPlayed = 'heavily_played';
    case ModeratelyPlayed = 'moderately_played';
    case LightlyPlayed = 'lightly_played';
    case NearMint = 'near_mint';

    public function label(): string
    {
        return match ($this) {
            self::Damaged => 'Damaged',
            self::HeavilyPlayed => 'Heavily Played',
            self::ModeratelyPlayed => 'Moderately Played',
            self::LightlyPlayed => 'Lightly Played',
            self::NearMint => 'Near Mint',
        };
    }
}
