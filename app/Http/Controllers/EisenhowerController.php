<?php

namespace App\Http\Controllers;

use App\Enums\Importance;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EisenhowerController extends Controller
{
    public function __construct(
        protected TaskRepositoryInterface $taskRepository,
        protected TagRepositoryInterface $tagRepository
    ) {}

    public function index()
    {
        $userId = Auth::id();
        $tasks = $this->taskRepository->getByQuadrants($userId);
        $tags = $this->tagRepository->getByUser($userId);

        $quadrants = [
            'do'       => $tasks->filter(fn($t) => $t->getQuadrant() === 'do')->values(),
            'schedule' => $tasks->filter(fn($t) => $t->getQuadrant() === 'schedule')->values(),
            'delegate' => $tasks->filter(fn($t) => $t->getQuadrant() === 'delegate')->values(),
            'delete'   => $tasks->filter(fn($t) => $t->getQuadrant() === 'delete')->values(),
        ];

        return view('eisenhower.index', compact('quadrants', 'tags'));
    }

    public function move(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'quadrant' => 'required|string|in:do,schedule,delegate,delete',
        ]);

        $today = now()->toDateString();

        $update = match($data['quadrant']) {
            'do'       => ['importance' => Importance::High,   'due_date' => $today],
            'schedule' => ['importance' => Importance::High],
            'delegate' => ['importance' => Importance::Low,    'due_date' => $today],
            'delete'   => ['importance' => Importance::Low,    'due_date' => null],
        };

        $this->taskRepository->update($task, $update);

        return response()->json(['success' => true]);
    }
}
