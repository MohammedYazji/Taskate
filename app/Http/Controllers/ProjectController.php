<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\Request;
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

        $tasks = $project->tasks()->with('tags', 'section', 'subtasks', 'comments.user', 'assignedTo')->get();
        $tags = $this->tagRepository->getByUser(Auth::id());
        $sections = $project->sections()->orderBy('position')->get();
        $projects = Project::forUser(Auth::id())->with('sections')->orderBy('name')->get();

        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'icon' => $project->icon,
                'color' => $project->color,
                'description' => $project->description,
            ],
            'currentUserRole' => $project->role(Auth::user()),
            'members' => $this->serializeMembers($project),
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
                'assigned_to_id' => $t->assigned_to_id,
                'assigned_to_name' => $t->assignedTo?->name,
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

    // === Owner + accepted collaborators, for the members panel and assignee picker ===
    private function serializeMembers(Project $project): array
    {
        $owner = [
            'id' => $project->user->id,
            'member_id' => null,
            'name' => $project->user->name,
            'avatar' => $project->user->avatar,
            'role' => 'owner',
        ];

        $members = $project->members()->get()->map(fn($u) => [
            'id' => $u->id,
            'member_id' => $u->pivot->id,
            'name' => $u->name,
            'avatar' => $u->avatar,
            'role' => $u->pivot->role,
        ])->all();

        return array_merge([$owner], $members);
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

        $name = $project->name;
        $this->projectRepository->delete($project);

        return redirect()->route('projects.index')->with('success', "Project \"{$name}\" deleted");
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

        return back()->with('success', "Project duplicated as \"{$newProject->name}\"");
    }

    // === Toggle pin ===
    public function pin(Project $project)
    {
        $this->authorize('update', $project);

        $project->update(['pinned' => !$project->pinned]);

        return back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'projects' => 'required|array',
            'projects.*.id' => 'required|integer|exists:projects,id',
            'projects.*.position' => 'required|integer',
            'projects.*.folder_id' => 'nullable|integer|exists:folders,id',
        ]);

        $ids = collect($validated['projects'])->pluck('id');
        $projects = Project::whereIn('id', $ids)->get();

        foreach ($projects as $project) {
            $this->authorize('update', $project);
        }

        foreach ($validated['projects'] as $item) {
            $update = ['position' => $item['position']];
            if (array_key_exists('folder_id', $item)) {
                $update['folder_id'] = $item['folder_id'];
            }
            Project::where('id', $item['id'])->update($update);
        }

        return back();
    }
}
