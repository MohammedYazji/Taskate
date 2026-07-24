<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Support\Facades\Auth;

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
        return view('projects.index', compact('projects'));
    }

    // === Create a new project ===
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $project = $this->projectRepository->create(array_merge($data, ['user_id' => Auth::id()]));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($project);
        }

        return redirect()->route('projects.index');
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

        $tasks = $project->tasks()->with('tags')->get();
        $tags = $this->tagRepository->getByUser(Auth::id());

        return view('projects.show', compact('project', 'tasks', 'tags'));
    }

    // === Update a project ===
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validated();

        $this->projectRepository->update($project, $data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($project);
        }

        return redirect()->route('projects.index');
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

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($newProject);
        }

        return redirect()->route('projects.show', $newProject);
    }

    // === Toggle pin ===
    public function pin(Project $project)
    {
        $this->authorize('update', $project);

        $project->update(['pinned' => !$project->pinned]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['pinned' => $project->pinned]);
        }

        return back();
    }
}
