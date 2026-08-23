<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Repositories\Interfaces\SprintRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SprintController extends Controller
{
    public function __construct(
        protected SprintRepositoryInterface $sprintRepository
    ) {}

    // === List all sprints for a project ===
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $sprints = $this->sprintRepository->getByProject($project->id);
        $activeSprint = $this->sprintRepository->getActive($project->id);

        $serializeSprint = fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'goal' => $s->goal,
            'status' => $s->status->value,
            'start_date' => $s->start_date->format('Y-m-d'),
            'end_date' => $s->end_date->format('Y-m-d'),
        ];

        return Inertia::render('Sprints/Index', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'sprints' => $sprints->map($serializeSprint)->values(),
            'activeSprint' => $activeSprint ? $serializeSprint($activeSprint) : null,
        ]);
    }

    // === Create a new sprint for a project ===
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'goal' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $this->sprintRepository->create([
            'project_id' => $project->id,
            ...$data,
        ]);

        return redirect()->route('projects.sprints.index', $project);
    }

    // === Update a sprint ===
    public function update(Request $request, Sprint $sprint)
    {
        $this->authorize('update', $sprint);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'goal' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $this->sprintRepository->update($sprint, $data);

        return redirect()->route('projects.sprints.index', $sprint->project_id);
    }

    // === Delete a sprint (tasks fall back to backlog) ===
    public function destroy(Sprint $sprint)
    {
        $this->authorize('delete', $sprint);

        $projectId = $sprint->project_id;
        $this->sprintRepository->delete($sprint);

        return redirect()->route('projects.sprints.index', $projectId);
    }

    // === Activate a sprint (completes any other active sprint) ===
    public function activate(Sprint $sprint)
    {
        $this->authorize('activate', $sprint);

        $this->sprintRepository->activate($sprint);

        return redirect()->route('projects.sprints.index', $sprint->project_id);
    }
}
