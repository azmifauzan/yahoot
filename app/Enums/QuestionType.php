<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case Poll = 'poll';

    /**
     * Whether answers to this question type are scored (points/streak).
     */
    public function isScored(): bool
    {
        return $this !== self::Poll;
    }
}
