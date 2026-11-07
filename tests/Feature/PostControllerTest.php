<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Pagination\LengthAwarePaginator;

test('guests can see the empty post list on the home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('아직 게시글이 없습니다.');
});

test('the post list paginates newest first with a stable order', function () {
    $this->freezeTime();
    $posts = Post::factory()->count(16)
        ->recycle(User::factory()->create())
        ->sequence(fn (Sequence $sequence): array => ['title' => '게시글 '.$sequence->index.' 제목'])
        ->create();

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertViewHas('posts', function (LengthAwarePaginator $paginated) use ($posts): bool {
            return $paginated->total() === 16
                && $paginated->getCollection()->modelKeys() === $posts->reverse()->take(15)->values()->modelKeys();
        })
        ->assertDontSeeText($posts->first()->title);
});

test('the next page contains the remaining post', function () {
    $this->freezeTime();
    $posts = Post::factory()->count(16)
        ->recycle(User::factory()->create())
        ->sequence(fn (Sequence $sequence): array => ['title' => '게시글 '.$sequence->index.' 제목'])
        ->create();

    $this->get(route('posts.index', ['page' => 2]))
        ->assertOk()
        ->assertSeeText($posts->first()->title)
        ->assertViewHas('posts', fn (LengthAwarePaginator $paginated): bool => $paginated->count() === 1);
});

test('the post list loads all authors in three queries without N plus one queries', function () {
    $posts = Post::factory()->count(5)->create();
    $names = User::query()->pluck('name')->all();
    $this->expectsDatabaseQueryCount(3);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSeeText($posts->pluck('title')->all())
        ->assertSeeText($names);
});

test('guests must log in before opening the post form', function () {
    $this->get(route('posts.create'))
        ->assertRedirectToRoute('login');
});

test('guests cannot create posts', function () {
    $this->post(route('posts.store'), [
        'title' => 'Guest post',
        'body' => 'This must not be saved.',
    ])->assertRedirectToRoute('login');

    $this->assertDatabaseCount('posts', 0);
});

test('authenticated users can open the post form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('posts.create'))
        ->assertOk()
        ->assertSeeText('게시글 작성');
});

test('authenticated users can publish a post', function (string $title, string $body) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('posts.store'), [
        'title' => $title,
        'body' => $body,
    ]);

    $post = Post::query()->sole();
    $response->assertRedirectToRoute('posts.show', $post)
        ->assertSessionHas('status', '게시글이 등록되었습니다.');
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'user_id' => $user->id,
        'title' => $title,
        'body' => $body,
    ]);
})->with([
    'ordinary content' => ['첫 번째 게시글', "게시글 내용입니다.\n두 번째 줄입니다."],
    'maximum lengths' => [str_repeat('제', 255), str_repeat('내', 10000)],
]);

test('submitted ownership and other unexpected fields cannot override server values', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($author)->post(route('posts.store'), [
        'title' => 'My post',
        'body' => 'Written by the signed-in user.',
        'user_id' => $otherUser->id,
        'id' => 99999,
        'created_at' => '2000-01-01 00:00:00',
    ]);

    $post = Post::query()->sole();
    $response->assertRedirectToRoute('posts.show', $post);
    expect($post->user->is($author))->toBeTrue();
    expect($post->id)->not->toBe(99999);
    expect($post->created_at->year)->not->toBe(2000);
    expect($otherUser->posts()->count())->toBe(0);
});

test('posts require a title and a body', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('posts.store'), [])
        ->assertSessionHasErrors([
            'title' => 'The title field is required.',
            'body' => 'The body field is required.',
        ]);

    $this->assertDatabaseCount('posts', 0);
});

test('invalid post content is rejected without saving', function (string $field, mixed $value, string $message) {
    $user = User::factory()->create();
    $payload = ['title' => 'Valid title', 'body' => 'Valid body'];
    $payload[$field] = $value;

    $this->actingAs($user)
        ->post(route('posts.store'), $payload)
        ->assertSessionHasErrors([$field => $message]);

    $this->assertDatabaseCount('posts', 0);
})->with([
    'title must be text' => ['title', ['invalid'], 'The title field must be a string.'],
    'body must be text' => ['body', ['invalid'], 'The body field must be a string.'],
    'title is too long' => ['title', str_repeat('a', 256), 'The title field must not be greater than 255 characters.'],
    'body is too long' => ['body', str_repeat('a', 10001), 'The body field must not be greater than 10000 characters.'],
    'title cannot be whitespace' => ['title', '   ', 'The title field is required.'],
    'body cannot be whitespace' => ['body', '   ', 'The body field is required.'],
]);

test('the post form shows validation errors and preserves entered content', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('posts.create'))
        ->followingRedirects()
        ->post(route('posts.store'), ['title' => 'Keep this title', 'body' => ''])
        ->assertOk()
        ->assertSee('Keep this title')
        ->assertSeeText('The body field is required.');

    $this->assertDatabaseCount('posts', 0);
});

test('guests can read a post and its author', function () {
    $post = Post::factory()->create();

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSeeText([$post->title, $post->body, $post->user->name]);
});

test('missing posts return not found', function () {
    $this->get(route('posts.show', 99999))
        ->assertNotFound();
});

test('post pages escape titles and author names', function (string $routeName) {
    $user = User::factory()->create(['name' => '<img src=x onerror=alert(1)>']);
    $post = Post::factory()->for($user)->create([
        'title' => '<script>alert("title")</script>',
    ]);

    $this->get(route($routeName, $post))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee($user->name)
        ->assertDontSee($post->title, false)
        ->assertDontSee($user->name, false);
})->with(['posts.index', 'posts.show']);

test('post details escape the post body', function () {
    $post = Post::factory()->create(['body' => '<script>alert("body")</script>']);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee($post->body)
        ->assertDontSee($post->body, false);
});

test('deleting a user removes their posts without affecting another author', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();
    $otherPost = Post::factory()->create();

    $user->delete();

    $this->assertModelMissing($post);
    $this->assertModelExists($otherPost);
});
