<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiQuestionService
{
    /**
     * Generate questions using Claude API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function generate(string $topic, int $count, string $difficulty, string $type, string $locale): array
    {
        $apiKey = config('services.anthropic.key');
        $apiUrl = config('services.anthropic.url');

        if (! $apiKey) {
            throw new \Exception('Anthropic API key is not configured.');
        }

        $systemPrompt = "You are a quiz generation expert. Your task is to generate trivia/quiz questions based on a topic in JSON format.
You must return ONLY a JSON array of questions. Do not wrap it in ```json blocks or include any other text, comments, markdown, or explanations. Just a raw JSON array.

Each question must be a JSON object with this schema:
{
  \"type\": \"multiple_choice\" or \"true_false\",
  \"question_text\": \"The text of the question\",
  \"time_limit\": integer (seconds, e.g., 20 or 30),
  \"points\": \"standard\" or \"double\" or \"none\",
  \"answers\": [
    {
      \"answer_text\": \"The text of the answer\",
      \"is_correct\": boolean,
      \"color\": \"red\" or \"blue\" or \"yellow\" or \"green\"
    }
  ]
}

For color assignment:
- For multiple_choice: answers must have distinct colors in the order: red, blue, yellow, green.
- For true_false: answers must have exactly two answers: True (color red) and False (color blue).
Ensure exactly one answer has `is_correct = true`.
Ensure the language of the output matches the requested locale ('id' for Indonesian, 'en' for English).";

        $userPrompt = "Generate {$count} {$difficulty} difficulty questions about '{$topic}' in language '{$locale}'. Question type: {$type}.";

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post($apiUrl, [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 4000,
            'system' => $systemPrompt,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $userPrompt,
                ],
            ],
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            Log::error('Claude API call failed: '.$response->body());
            throw new \Exception('Failed to generate questions using AI: '.$response->status());
        }

        $result = $response->json();
        $text = $result['content'][0]['text'] ?? '';

        $text = trim($text);
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/```$/', '', $text);
            $text = trim($text);
        }

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            Log::error('Failed to decode JSON from Claude response: '.$text);
            throw new \Exception('AI returned invalid JSON structure.');
        }

        return $decoded;
    }
}
