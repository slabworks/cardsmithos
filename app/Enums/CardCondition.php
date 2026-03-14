<?php

namespace App\Enums;

enum CardCondition: string
{
    case NearMint = 'near_mint';
    case LightlyPlayed = 'lightly_played';
    case ModeratelyPlayed = 'moderately_played';
    case HeavilyPlayed = 'heavily_played';
    case Damaged = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::NearMint => 'Near Mint',
            self::LightlyPlayed => 'Lightly Played',
            self::ModeratelyPlayed => 'Moderately Played',
            self::HeavilyPlayed => 'Heavily Played',
            self::Damaged => 'Damaged',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NearMint => 'emerald',
            self::LightlyPlayed => 'green',
            self::ModeratelyPlayed => 'yellow',
            self::HeavilyPlayed => 'orange',
            self::Damaged => 'red',
        };
    }
}
