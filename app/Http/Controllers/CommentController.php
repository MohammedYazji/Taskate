<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(
        protected CommentRepositoryInterface $commentRepository
    ) {}

    public function store(Request $request, Task $task)
    {
        $data = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $this->commentRepository->create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body' => $data['body'],
        ]);

        return redirect()->back();
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $this->commentRepository->delete($comment);

        return redirect()->back();
    }
}
