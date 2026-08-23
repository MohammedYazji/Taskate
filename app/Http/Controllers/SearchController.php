<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository
    ) {}

    public function index(Request $request)
    {
        $query = $request->get('q', '');

        $tasks = $query
            ? $this->taskRepository->search(Auth::id(), $query)
            : collect();

        return Inertia::render('Search', [
            'tasks' => $tasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status->value,
                'priority' => $t->priority->value,
                'project_name' => $t->project?->name ?? '',
            ]),
            'query' => $query,
        ]);
    }
}
