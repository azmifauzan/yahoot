<?php

namespace App\Enums;

enum SoundTheme: string
{
    case Classic = 'classic';
    case Chill = 'chill';
    case Energetic = 'energetic';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Classic => 'Classic',
            self::Chill => 'Chill',
            self::Energetic => 'Energetic',
            self::None => 'None',
        };
    }
}
