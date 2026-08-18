<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $folder = Folder::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
        ]);

        return back();
    }

    public function update(Request $request, Folder $folder)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $folder->update(['name' => $request->name]);

        return back();
    }

    public function destroy(Folder $folder)
    {
        $folder->projects()->update(['folder_id' => null]);
        $folder->delete();

        return back();
    }

    public function pin(Folder $folder)
    {
        $folder->update(['pinned' => !$folder->pinned]);

        return back();
    }
}
