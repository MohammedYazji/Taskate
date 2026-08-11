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
        $today = now()->toDateString();
        $prompt = <<<PROMPT
You are a project management assistant. Break down the following topic into sections with tasks and subtasks.

Topic: "{$topic}"

Today's date is {$today}.

Return a JSON object with:
- "project_name": A short, concise project name (max 30 characters)
- "sections": An array of sections, each with:
  - "name": A clear section name (e.g. "Backend", "Frontend", "Testing", "Deployment")
  - "tasks": An array of tasks, each with:
    - "title": A clear, actionable task title
    - "description": A detailed description in Markdown format. Use headers (##), bullet lists (-), code blocks (```), and bold (**). Explain the task scope, key considerations, and acceptance criteria. Keep it concise but useful (3-8 lines).
    - "priority": One of "low", "medium", or "high"
    - "due_date": A realistic due date in "YYYY-MM-DD" format. Spread tasks over 1-3 weeks. Earlier sections get earlier dates. High priority tasks come first.
    - "subtasks": An array of 1-5 small, concrete subtasks (strings). Each subtask is a short step toward completing the task. Leave empty array [] if the task is simple.

Create 2-5 sections with 2-4 tasks each. Each task should have 1-5 meaningful subtasks.
Return ONLY the JSON object, no markdown, no explanation.

Example:
{"project_name":"Blog Platform","sections":[{"name":"Setup","tasks":[{"title":"Initialize repository","description":"Create Git repo and README","priority":"high","due_date":"2026-07-18","subtasks":["Create GitHub repo","Add README.md","Set up .gitignore"]}]},{"name":"Backend","tasks":[{"title":"Create user auth","description":"Registration and login","priority":"high","due_date":"2026-07-22","subtasks":["Create User model","Add auth routes","Build login controller","Add password hashing"]}]}]}
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
                'maxOutputTokens' => 8192,
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        $data = $response->json();
        $text = '';
        foreach ($data['candidates'][0]['content']['parts'] ?? [] as $part) {
            if (isset($part['thought']) && $part['thought']) {
                continue;
            }
            $text .= $part['text'] ?? '';
        }

        $text = trim($text);
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);

        $result = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse Gemini response as JSON: ' . substr($text, 0, 500));
        }

        if (!isset($result['project_name']) || !isset($result['sections'])) {
            throw new \Exception('Invalid response structure from Gemini');
        }

        foreach ($result['sections'] as &$section) {
            if (isset($section['tasks'])) {
                foreach ($section['tasks'] as &$task) {
                    if (isset($task['subtasks']) && is_array($task['subtasks'])) {
                        $task['subtasks'] = array_map(function ($sub) {
                            if (is_string($sub)) {
                                return ['title' => $sub, 'approved' => true];
                            }
                            $sub['approved'] = $sub['approved'] ?? true;
                            $sub['title'] = $sub['title'] ?? '';
                            return $sub;
                        }, $task['subtasks']);
                    } else {
                        $task['subtasks'] = [];
                    }
                }
            }
        }

        return $result;
    }
}
