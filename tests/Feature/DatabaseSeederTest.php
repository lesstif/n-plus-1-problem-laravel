<?php

use App\Models\Post;
use App\Models\User;

test('seeding creates ten users with five to twenty posts per user', function () {
    $this->seed();

    $this->assertDatabaseCount('users', 10);
    $users = User::query()->withCount('posts')->get();

    foreach ($users as $user) {
        expect($user->posts_count)->toBeBetween(5, 20);
    }

    $this->assertDatabaseCount('posts', $users->sum('posts_count'));
    expect(Post::query()->whereDoesntHave('user')->count())->toBe(0);
});

test('the seeded demo account can log in and publish a post', function () {
    $this->seed();
    $postCount = Post::query()->count();
    $user = User::query()->where('email', 'test@example.com')->sole();

    $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ])->assertRedirectToRoute('posts.index');

    $this->assertAuthenticatedAs($user);
    $this->post(route('posts.store'), [
        'title' => 'Seeded user post',
        'body' => 'A post written after seeding.',
    ])->assertRedirectToRoute('posts.show', Post::query()->latest('id')->first());

    $this->assertDatabaseCount('posts', $postCount + 1);
    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'title' => 'Seeded user post',
        'body' => 'A post written after seeding.',
    ]);
});
