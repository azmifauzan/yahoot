<?php

namespace App\Enums;

enum LlmProvider: string
{
    case OpenAI = 'openai';
    case Anthropic = 'anthropic';

    public function defaultBaseUrl(): string
    {
        return match ($this) {
            self::OpenAI => 'https://api.openai.com/v1',
            self::Anthropic => 'https://api.anthropic.com/v1',
        };
    }

    public function defaultModel(): string
    {
        return match ($this) {
            self::OpenAI => 'gpt-4o-mini',
            self::Anthropic => 'claude-3-5-haiku-latest',
        };
    }
}
