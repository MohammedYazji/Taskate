<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
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

        $this->projectRepository->create(array_merge($data, ['user_id' => Auth::id()]));

        return redirect()->route('projects.index');
    }

    // === Show a single project with its tasks ===
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()->with('tags')->get();
        $tags = $this->tagRepository->getByUser(Auth::id());

        return view('projects.show', compact('project', 'tasks', 'tags'));
    }

    // === Update a project ===
    public function update(StoreProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validated();

        $this->projectRepository->update($project, $data);

        return redirect()->route('projects.index');
    }

    // === Delete a project ===
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $this->projectRepository->delete($project);

        return redirect()->route('projects.index');
    }
}
