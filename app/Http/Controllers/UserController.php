<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->select(['id', 'name'])
            ->withCount('posts')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15);

        return view('users.index', ['users' => $users]);
    }

    public function show(User $user): View
    {
        $posts = $user->posts()
            ->select(['id', 'user_id', 'title', 'created_at'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(15);

        return view('users.show', ['user' => $user, 'posts' => $posts]);
    }
}
