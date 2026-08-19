<?php

namespace App\Http\Middleware;

use App\Models\Folder;
use App\Models\Tag;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $sidebar = fn () => [
            'next7Count' => fn () => 0,
            'inboxCount' => fn () => 0,
        ];

        if ($user) {
            $projectRepo = app(ProjectRepositoryInterface::class);
            $tagRepo = app(TagRepositoryInterface::class);

            $sidebar = fn () => $this->buildSidebar($user, $projectRepo, $tagRepo);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'ziggy' => [
                'location' => $request->url(),
                ...(new \Tighten\Ziggy\Ziggy())->toArray(),
            ],
            'projects' => fn () => $user ? $this->getProjects($user, app(ProjectRepositoryInterface::class)) : [],
            'tags' => fn () => $user ? $this->getTags($user, app(TagRepositoryInterface::class)) : [],
            'folders' => fn () => $user ? $this->getFolders($user) : [],
            'sidebar' => $sidebar,
        ];
    }

    private function buildSidebar($user, $projectRepo, $tagRepo)
    {
        $projects = $this->getProjects($user, $projectRepo);
        $tags = $this->getTags($user, $tagRepo);
        $folders = $this->getFolders($user);

        $inboxProject = $projects->firstWhere('name', 'Inbox');
        $inboxCount = $inboxProject ? $inboxProject->tasks_count : 0;

        return [
            'projects' => $projects,
            'tags' => $tags,
            'folders' => $folders,
            'next7Count' => 0,
            'inboxCount' => $inboxCount,
        ];
    }

    private function getProjects($user, $projectRepo)
    {
        return $projectRepo->getWithTaskCounts($user->id);
    }

    private function getTags($user, $tagRepo)
    {
        return $tagRepo->getByUser($user->id);
    }

    private function getFolders($user)
    {
        return Folder::where('user_id', $user->id)
            ->with('projects')
            ->orderBy('pinned', 'desc')
            ->orderBy('position')
            ->get();
    }
}
