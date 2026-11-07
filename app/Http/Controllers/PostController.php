<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->select(['id', 'user_id', 'title', 'created_at'])
            ->with('user:id,name')
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);

        return view('posts.index', ['posts' => $posts]);
    }

    public function create(): View
    {
        return view('posts.create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create($request->validated());

        return to_route('posts.show', $post)
            ->with('status', '게시글이 등록되었습니다.');
    }

    public function show(Post $post): View
    {
        return view('posts.show', ['post' => $post->load('user:id,name')]);
    }
}
