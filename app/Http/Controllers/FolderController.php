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
        $this->authorize('update', $folder);

        $request->validate(['name' => 'required|string|max:100']);

        $folder->update(['name' => $request->name]);

        return back();
    }

    public function destroy(Folder $folder)
    {
        $this->authorize('delete', $folder);

        $folder->projects()->update(['folder_id' => null]);
        $folder->delete();

        return back()->with('success', 'Folder deleted');
    }

    public function pin(Folder $folder)
    {
        $this->authorize('update', $folder);

        $folder->update(['pinned' => !$folder->pinned]);

        return back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'folders' => 'required|array',
            'folders.*.id' => 'required|integer|exists:folders,id',
            'folders.*.position' => 'required|integer',
        ]);

        $ids = collect($validated['folders'])->pluck('id');
        $folders = Folder::whereIn('id', $ids)->get();

        foreach ($folders as $folder) {
            $this->authorize('update', $folder);
        }

        foreach ($validated['folders'] as $item) {
            Folder::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return back();
    }
}
