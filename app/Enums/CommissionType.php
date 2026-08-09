<?php

namespace App\Enums;

enum CommissionType: string
{
    case Sponsor = 'sponsor';
    case Matching = 'matching';
    case Level = 'level';
    case Royalty = 'royalty';

    public function label(): string
    {
        return match ($this) {
            self::Sponsor => 'Sponsor Bonus',
            self::Matching => 'Matching Bonus',
            self::Level => 'Level Commission',
            self::Royalty => 'Royalty Bonus',
        };
    }
}
