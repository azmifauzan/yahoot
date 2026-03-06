<?php

namespace App\Enums;

enum QuizTheme: string
{
    case Standard = 'standard';
    case Ocean = 'ocean';
    case Sunset = 'sunset';
    case Forest = 'forest';
    case Galaxy = 'galaxy';
    case Candy = 'candy';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Ocean => 'Ocean',
            self::Sunset => 'Sunset',
            self::Forest => 'Forest',
            self::Galaxy => 'Galaxy',
            self::Candy => 'Candy',
        };
    }

    /**
     * @return array{lobby: string, question: string, scoreboard: string, finished: string}
     */
    public function gradients(): array
    {
        return match ($this) {
            self::Standard => [
                'lobby' => 'from-indigo-600 to-purple-700',
                'question' => 'bg-gray-900',
                'scoreboard' => 'from-indigo-600 to-purple-700',
                'finished' => 'from-yellow-400 via-pink-500 to-purple-600',
            ],
            self::Ocean => [
                'lobby' => 'from-cyan-600 to-blue-800',
                'question' => 'bg-slate-900',
                'scoreboard' => 'from-cyan-600 to-blue-800',
                'finished' => 'from-cyan-400 via-blue-500 to-indigo-600',
            ],
            self::Sunset => [
                'lobby' => 'from-orange-500 to-rose-600',
                'question' => 'bg-stone-900',
                'scoreboard' => 'from-orange-500 to-rose-600',
                'finished' => 'from-amber-400 via-orange-500 to-rose-600',
            ],
            self::Forest => [
                'lobby' => 'from-emerald-600 to-teal-800',
                'question' => 'bg-zinc-900',
                'scoreboard' => 'from-emerald-600 to-teal-800',
                'finished' => 'from-lime-400 via-emerald-500 to-teal-600',
            ],
            self::Galaxy => [
                'lobby' => 'from-violet-700 to-fuchsia-900',
                'question' => 'bg-neutral-950',
                'scoreboard' => 'from-violet-700 to-fuchsia-900',
                'finished' => 'from-fuchsia-400 via-violet-500 to-purple-700',
            ],
            self::Candy => [
                'lobby' => 'from-pink-500 to-rose-600',
                'question' => 'bg-gray-900',
                'scoreboard' => 'from-pink-500 to-rose-600',
                'finished' => 'from-pink-400 via-rose-400 to-fuchsia-500',
            ],
        };
    }
}
