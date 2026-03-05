<?php

namespace App\Enums;

enum AnswerColor: string
{
    case Red = 'red';
    case Blue = 'blue';
    case Yellow = 'yellow';
    case Green = 'green';

    public function shape(): string
    {
        return match ($this) {
            self::Red => 'triangle',
            self::Blue => 'diamond',
            self::Yellow => 'circle',
            self::Green => 'square',
        };
    }

    public function hex(): string
    {
        return match ($this) {
            self::Red => '#FF6B6B',
            self::Blue => '#5B8DEF',
            self::Yellow => '#FECA57',
            self::Green => '#48DBAB',
        };
    }
}
