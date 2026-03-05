<?php

namespace App\Enums;

enum PointType: string
{
    case Standard = 'standard';
    case Double = 'double';
    case None = 'none';

    public function basePoints(): int
    {
        return match ($this) {
            self::Standard => 1000,
            self::Double => 2000,
            self::None => 0,
        };
    }
}
