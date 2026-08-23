<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectRepositoryInterface $projectRepository,
        protected TaskRepositoryInterface $taskRepository,
        protected TagRepositoryInterface $tagRepository
    )
    {}

    // === Show all projects with task counts ===
    public function index()
    {
        $projects = $this->projectRepository->getWithTaskCounts(Auth::id());

        return Inertia::render('Projects/Index', [
            'projects' => $projects->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'color' => $p->color,
                'description' => $p->description,
                'tasks_count' => $p->tasks_count,
                'completed_tasks_count' => $p->completed_tasks_count,
                'updated_at' => $p->updated_at->diffForHumans(),
            ]),
        ]);
    }

    // === Create a new project ===
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $project = $this->projectRepository->create(array_merge($data, ['user_id' => Auth::id()]));

        return back();
    }

    // === Show a single project with its tasks ===
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        if ($project->view_type === 'kanban') {
            return redirect()->route('projects.board', $project);
        }

        if ($project->view_type === 'timeline') {
            return redirect()->route('calendar', ['project_id' => $project->id]);
        }

        $tasks = $project->tasks()->with('tags', 'section', 'subtasks', 'comments.user')->get();
        $tags = $this->tagRepository->getByUser(Auth::id());
        $sections = $project->sections()->orderBy('position')->get();
        $projects = \App\Models\Project::where('user_id', Auth::id())->with('sections')->orderBy('name')->get();

        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'icon' => $project->icon,
                'color' => $project->color,
                'description' => $project->description,
            ],
            'sections' => $sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
            'tasks' => $tasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'priority' => $t->priority->value,
                'status' => $t->status->value,
                'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : '',
                'is_recurring' => $t->is_recurring,
                'section_id' => $t->section_id,
                'project_id' => $t->project_id,
                'tag_ids' => $t->tags->pluck('id')->toArray(),
                'subtasks' => $t->subtasks->map(fn($s) => ['id' => $s->id, 'title' => $s->title, 'is_completed' => $s->is_completed])->toArray(),
                'comments' => $t->comments->map(fn($c) => ['id' => $c->id, 'body' => $c->body, 'user_name' => $c->user->name ?? '', 'created_at' => $c->created_at->diffForHumans()])->toArray(),
            ]),
            'tags' => $tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color]),
            'projects' => $projects->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'icon' => $p->icon,
                'sections' => $p->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
            ]),
        ]);
    }

    // === Update a project ===
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validated();

        $this->projectRepository->update($project, $data);

        return back();
    }

    // === Delete a project ===
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $this->projectRepository->delete($project);

        return redirect()->route('projects.index');
    }

    // === Duplicate a project ===
    public function duplicate(Project $project)
    {
        $this->authorize('view', $project);

        $newProject = $this->projectRepository->create([
            'user_id' => Auth::id(),
            'name' => $project->name . ' (Copy)',
            'color' => $project->color,
            'view_type' => $project->view_type,
        ]);

        return back();
    }

    // === Toggle pin ===
    public function pin(Project $project)
    {
        $this->authorize('update', $project);

        $project->update(['pinned' => !$project->pinned]);

        return back();
    }
}
