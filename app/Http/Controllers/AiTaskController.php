<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiTaskController extends Controller
{
    public function __construct(
        protected GeminiService $geminiService
    ) {}

    public function showForm()
    {
        return view('ai.generate');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|min:5|max:500',
        ]);

        try {
            $result = $this->geminiService->breakTopicIntoTasks($request->topic);

            session(['ai_generated_tasks' => $result['tasks'], 'ai_topic' => $request->topic]);

            return view('ai.review', [
                'tasks' => $result['tasks'],
                'projectName' => $result['project_name'],
                'topic' => $request->topic,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['topic' => 'Failed to generate tasks: ' . $e->getMessage()])->withInput();
        }
    }

    public function approve(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'tasks' => 'required|array',
            'tasks.*.title' => 'required|string',
            'tasks.*.description' => 'nullable|string',
            'tasks.*.priority' => 'required|in:low,medium,high',
            'tasks.*.approved' => 'nullable|boolean',
        ]);

        $user = Auth::id();
        $created = 0;

        $project = Project::create([
            'user_id' => $user,
            'name' => $request->project_name,
            'color' => '#7c3aed',
        ]);

        foreach ($request->tasks as $taskData) {
            if (empty($taskData['approved'])) {
                continue;
            }

            Task::create([
                'user_id' => $user,
                'project_id' => $project->id,
                'title' => $taskData['title'],
                'description' => $taskData['description'] ?? null,
                'priority' => $taskData['priority'],
                'status' => 'todo',
            ]);

            $created++;
        }

        session()->forget(['ai_generated_tasks', 'ai_topic']);

        return redirect()->route('projects.board', $project)->with('success', "Created project '{$project->name}' with {$created} task" . ($created !== 1 ? 's' : ''));
    }
}
