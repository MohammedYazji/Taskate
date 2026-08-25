<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSearchController extends Controller
{
    // === Search users to invite to a project (excludes self, owner, existing/pending members) ===
    public function forProject(Request $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $query = trim((string) $request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $excludeIds = array_merge(
            [Auth::id(), $project->user_id],
            $project->members()->pluck('users.id')->toArray(),
            $project->invitations()->where('status', 'pending')->pluck('invited_user_id')->toArray()
        );

        $users = User::where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%");
            })
            ->whereNotIn('id', $excludeIds)
            ->limit(10)
            ->get(['id', 'name', 'email', 'avatar']);

        return response()->json($users);
    }
}
