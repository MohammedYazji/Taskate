<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxPosition = $project->sections()->max('position') ?? 0;

        $section = $project->sections()->create([
            'name' => $validated['name'],
            'position' => $maxPosition + 1,
        ]);

        return response()->json($section);
    }

    public function storeAbove(Request $request, Section $section)
    {
        $this->authorize('update', $section->project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $section->project->sections()
            ->where('position', '>=', $section->position)
            ->increment('position');

        $newSection = $section->project->sections()->create([
            'name' => $validated['name'],
            'position' => $section->position,
        ]);

        return response()->json($newSection);
    }

    public function storeBelow(Request $request, Section $section)
    {
        $this->authorize('update', $section->project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $section->project->sections()
            ->where('position', '>', $section->position)
            ->increment('position');

        $newSection = $section->project->sections()->create([
            'name' => $validated['name'],
            'position' => $section->position + 1,
        ]);

        return response()->json($newSection);
    }

    public function update(Request $request, Section $section)
    {
        $this->authorize('update', $section->project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $section->update(['name' => $validated['name']]);

        return response()->json($section);
    }

    public function destroy(Request $request, Section $section)
    {
        $this->authorize('update', $section->project);

        $targetSectionId = $request->input('target_section_id');
        $section->tasks()->update(['section_id' => $targetSectionId ?: null]);
        $section->delete();

        return response()->json(['success' => true]);
    }

    public function move(Request $request, Section $section)
    {
        $this->authorize('update', $section->project);

        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'section_id' => 'nullable|integer|exists:sections,id',
        ]);

        $section->tasks()->update(['section_id' => $validated['section_id'] ?? null]);
        $section->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|integer|exists:sections,id',
            'sections.*.position' => 'required|integer',
        ]);

        foreach ($validated['sections'] as $item) {
            Section::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }
}
