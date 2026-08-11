<?php

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiTaskController extends Controller
{
    public function showForm()
    {
        $folders = \App\Models\Folder::where('user_id', Auth::id())->orderBy('name')->get();

        return view('ai.generate', compact('folders'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|min:5|max:500',
            'folder_id' => 'nullable|integer|exists:folders,id',
        ]);

        $generation = AiGeneration::create([
            'user_id' => Auth::id(),
            'topic' => $request->topic,
            'folder_id' => $request->folder_id,
            'status' => 'processing',
        ]);

        try {
            $gemini = app(\App\Services\GeminiService::class);
            $result = $gemini->breakTopicIntoTasks($request->topic);

            $generation->update([
                'status' => 'completed',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            $generation->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('ai.status', $generation);
    }

    public function status(AiGeneration $generation)
    {
        if ($generation->user_id !== Auth::id()) {
            abort(403);
        }

        $generation->refresh();

        if ($generation->status === 'completed') {
            return view('ai.review', [
                'sections' => $generation->result['sections'] ?? [],
                'projectName' => $generation->result['project_name'] ?? '',
                'topic' => $generation->topic,
                'folderId' => $generation->folder_id,
                'folders' => \App\Models\Folder::where('user_id', Auth::id())->orderBy('name')->get(),
            ]);
        }

        return view('ai.status', compact('generation'));
    }

    public function approve(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'folder_id' => 'nullable|integer|exists:folders,id',
        ]);

        $user = Auth::id();
        $created = 0;

        $project = Project::create([
            'user_id' => $user,
            'name' => $request->project_name,
            'color' => '#7c3aed',
            'folder_id' => $request->folder_id,
        ]);

        $position = 0;
        foreach ($request->input('sections', []) as $sectionIndex => $sectionData) {
            if (empty($sectionData['approved'])) continue;

            $section = \App\Models\Section::create([
                'project_id' => $project->id,
                'name' => $sectionData['name'],
                'position' => $position++,
            ]);

            foreach ($sectionData['tasks'] ?? [] as $taskIndex => $taskData) {
                if (empty($taskData['approved'])) continue;

                $task = Task::create([
                    'user_id' => $user,
                    'project_id' => $project->id,
                    'section_id' => $section->id,
                    'title' => $taskData['title'],
                    'description' => !empty($taskData['description']) ? \Illuminate\Support\Str::markdown($taskData['description']) : '',
                    'priority' => $taskData['priority'] ?? 'medium',
                    'due_date' => !empty($taskData['due_date']) ? $taskData['due_date'] : null,
                    'position' => $position++,
                ]);

                if (!empty($taskData['subtasks'])) {
                    foreach ($taskData['subtasks'] as $subIndex => $subData) {
                        if (empty($subData['approved'])) continue;
                        $task->subtasks()->create([
                            'title' => $subData['title'],
                            'is_completed' => false,
                        ]);
                    }
                }

                $created++;
            }
        }

        return redirect()->route('projects.show', $project)->with('success', "Created project '{$project->name}' with {$created} task" . ($created !== 1 ? 's' : ''));
    }
}
