<?php

namespace App\Enums;

enum PowerUpType: string
{
    case DoublePoints = 'double_points';
    case FiftyFifty = 'fifty_fifty';
    case FreezeTimer = 'freeze_timer';

    public function label(): string
    {
        return match ($this) {
            self::DoublePoints => 'Double Points',
            self::FiftyFifty => 'Fifty-Fifty',
            self::FreezeTimer => 'Freeze Timer',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DoublePoints => '2️⃣',
            self::FiftyFifty => '✂️',
            self::FreezeTimer => '❄️',
        };
    }

    /**
     * The default power-ups every player starts a game with.
     *
     * @return array<int, string>
     */
    public static function defaults(): array
    {
        return array_map(fn (self $type) => $type->value, self::cases());
    }
}
