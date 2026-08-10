<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\TagRepositoryInterface;

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

        return view('tags.index', compact('tags'));
    }

    public function store(StoreTagRequest $request)
    {
        $data = $request->validated();

        $tag = $this->tagRepository->create(array_merge($data, ['user_id' => Auth::id()]));

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($tag);
        }
        return redirect()->route('tags.index');
    }

    public function update(StoreTagRequest $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $data = $request->validated();

        $this->tagRepository->update($tag, $data);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($tag->fresh());
        }
        return redirect()->route('tags.index');
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $this->tagRepository->delete($tag);

        if (request()->expectsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->noContent();
        }
        return redirect()->route('tags.index');
    }
}
