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
            // Namespaced under a key no page controller also uses, so a page's own
            // 'projects'/'tags'/'folders' props can never silently shadow the sidebar's.
            'layoutSidebar' => fn () => $user
                ? $this->buildSidebar($user, app(ProjectRepositoryInterface::class), app(TagRepositoryInterface::class))
                : ['projects' => [], 'tags' => [], 'folders' => [], 'next7Count' => 0, 'inboxCount' => 0],
        ];
    }

    private function buildSidebar($user, $projectRepo, $tagRepo)
    {
        $projects = $this->getProjects($user, $projectRepo);
        $tags = $this->getTags($user, $tagRepo);
        $folders = $this->getFolders($user);

        $inboxProject = $projects->firstWhere('name', 'Inbox');
        $inboxCount = $inboxProject ? $inboxProject->tasks_count : 0;
        $next7Count = \App\Models\Task::forUser($user->id)
            ->where('status', '!=', \App\Enums\TaskStatus::Done)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        return [
            'projects' => $projects,
            'tags' => $tags,
            'folders' => $folders,
            'next7Count' => $next7Count,
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
