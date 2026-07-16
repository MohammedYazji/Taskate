<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function breakTopicIntoTasks(string $topic): array
    {
        $prompt = <<<PROMPT
You are a project management assistant. Break down the following topic into actionable tasks.

Topic: "{$topic}"

Return a JSON object with:
- "project_name": A short, concise project name (max 30 characters)
- "tasks": An array of tasks, each with:
  - "title": A clear, actionable task title
  - "description": A brief description of what needs to be done
  - "priority": One of "low", "medium", or "high"

Create between 3-8 tasks. Order them from highest to lowest priority.
Return ONLY the JSON object, no markdown, no explanation.

Example format:
{"project_name":"E-Commerce API","tasks":[{"title":"Task 1","description":"Description 1","priority":"high"},{"title":"Task 2","description":"Description 2","priority":"medium"}]}
PROMPT;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024,
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $text = trim($text);
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);

        $tasks = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse Gemini response as JSON: ' . $text);
        }

        return $tasks;
    }
}
