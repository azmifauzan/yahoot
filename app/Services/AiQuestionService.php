<?php

namespace App\Services;

use App\Enums\LlmProvider;
use App\Exceptions\AiGenerationException;
use App\Models\LlmSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiQuestionService
{
    /**
     * Generate quiz question drafts using the user's configured LLM provider.
     *
     * @return array<int, array{question_text: string, answers: array<int, array{answer_text: string, is_correct: bool}>}>
     */
    public function generate(LlmSetting $settings, string $topic, int $count, string $difficulty, string $type, string $locale): array
    {
        $prompt = $this->buildPrompt($topic, $count, $difficulty, $type, $locale);

        $content = match ($settings->provider) {
            LlmProvider::OpenAI => $this->callOpenAi($settings, $prompt),
            LlmProvider::Anthropic => $this->callAnthropic($settings, $prompt),
        };

        return $this->parseQuestions($content, $type, $count);
    }

    private function buildPrompt(string $topic, int $count, string $difficulty, string $type, string $locale): string
    {
        $language = $locale === 'id' ? 'Indonesian' : 'English';
        $answerSpec = $type === 'true_false'
            ? 'exactly 2 answer options, "True"/"False" (translated to the requested language), with exactly 1 marked "is_correct": true'
            : 'exactly 4 answer options, with exactly 1 marked "is_correct": true';

        return <<<PROMPT
            Generate {$count} {$difficulty} difficulty quiz question(s) about "{$topic}" in {$language}.
            Each question must be of type "{$type}" with {$answerSpec}.

            Respond with ONLY a JSON object (no markdown, no extra text) matching this schema:
            {"questions": [{"question_text": "string", "answers": [{"answer_text": "string", "is_correct": boolean}]}]}
            PROMPT;
    }

    private function callOpenAi(LlmSetting $settings, string $prompt): string
    {
        $response = Http::withToken($settings->api_key)
            ->baseUrl($settings->resolvedBaseUrl())
            ->timeout(60)
            ->post('/chat/completions', [
                'model' => $settings->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a quiz question generator that responds only with valid JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            $this->throwProviderError($response);
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new AiGenerationException('AI provider returned an empty response.');
        }

        return $content;
    }

    private function callAnthropic(LlmSetting $settings, string $prompt): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $settings->api_key,
            'anthropic-version' => '2023-06-01',
        ])
            ->baseUrl($settings->resolvedBaseUrl())
            ->timeout(60)
            ->post('/messages', [
                'model' => $settings->model,
                'max_tokens' => 4096,
                'system' => 'You are a quiz question generator that responds only with valid JSON, no markdown.',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            $this->throwProviderError($response);
        }

        $content = $response->json('content.0.text');

        if (! is_string($content) || $content === '') {
            throw new AiGenerationException('AI provider returned an empty response.');
        }

        return $content;
    }

    private function throwProviderError(Response $response): never
    {
        Log::warning('AI provider request failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new AiGenerationException("AI provider request failed with status {$response->status()}. Please check your LLM settings and try again.");
    }

    /**
     * @return array<int, array{question_text: string, answers: array<int, array{answer_text: string, is_correct: bool}>}>
     */
    private function parseQuestions(string $content, string $type, int $count): array
    {
        $json = trim($content);

        // Strip markdown code fences some models add despite instructions.
        if (str_starts_with($json, '```')) {
            $json = preg_replace('/^```[a-zA-Z]*\n|```$/m', '', $json);
            $json = trim($json);
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded) || ! isset($decoded['questions']) || ! is_array($decoded['questions'])) {
            throw new AiGenerationException('AI provider returned an unexpected response format.');
        }

        $expectedAnswers = $type === 'true_false' ? 2 : 4;
        $questions = [];

        foreach (array_slice($decoded['questions'], 0, $count) as $question) {
            if (! is_array($question) || ! isset($question['question_text'], $question['answers']) || ! is_array($question['answers'])) {
                continue;
            }

            $answers = array_slice(array_values($question['answers']), 0, $expectedAnswers);

            if (count($answers) !== $expectedAnswers) {
                continue;
            }

            $normalizedAnswers = array_map(fn (array $answer) => [
                'answer_text' => (string) ($answer['answer_text'] ?? ''),
                'is_correct' => (bool) ($answer['is_correct'] ?? false),
            ], $answers);

            if (! collect($normalizedAnswers)->contains('is_correct', true)) {
                continue;
            }

            // Shuffle multiple-choice options so the correct answer is not
            // predictably positioned. True/False keeps its order to preserve the
            // blue=True / red=False colour convention.
            if ($type !== 'true_false') {
                shuffle($normalizedAnswers);
            }

            $questions[] = [
                'question_text' => (string) $question['question_text'],
                'answers' => $normalizedAnswers,
            ];
        }

        if ($questions === []) {
            throw new AiGenerationException('AI provider did not return any usable questions.');
        }

        return $questions;
    }
}
