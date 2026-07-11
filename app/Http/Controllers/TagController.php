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

        $this->tagRepository->create(array_merge($data, ['user_id' => Auth::id()]));

        return redirect()->route('tags.index');
    }

    public function update(StoreTagRequest $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $data = $request->validated();

        $this->tagRepository->update($tag, $data);

        return redirect()->route('tags.index');
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $this->tagRepository->delete($tag);

        return redirect()->route('tags.index');
    }
}
