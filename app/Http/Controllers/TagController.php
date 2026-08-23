<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Inertia\Inertia;

class TagController extends Controller
{
    protected $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function index()
    {
        $tags = $this->tagRepository->getByUser(Auth::id());

        return Inertia::render('Tags', [
            'tags' => $tags->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'color' => $t->color,
                'tasks_count' => $t->tasks_count ?? 0,
            ]),
        ]);
    }

    public function store(StoreTagRequest $request)
    {
        $data = $request->validated();

        $tag = $this->tagRepository->create(array_merge($data, ['user_id' => Auth::id()]));

        return back();
    }

    public function update(StoreTagRequest $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $data = $request->validated();

        $this->tagRepository->update($tag, $data);

        return back();
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $this->tagRepository->delete($tag);

        return back()->with('success', 'Tag deleted');
    }
}
