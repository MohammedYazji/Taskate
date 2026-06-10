<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:50',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $this->tagRepository->create(array_merge($data, ['user_id' => Auth::id()]));

        return redirect()->route('tags.index');
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:50',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $this->tagRepository->update($tag, $data);

        return redirect()->route('tags.index');
    }

    public function destroy(Tag $tag)
    {
        $this->tagRepository->delete($tag);

        return redirect()->route('tags.index');
    }
}
