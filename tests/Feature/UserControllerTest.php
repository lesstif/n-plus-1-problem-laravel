<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Pagination\LengthAwarePaginator;

test('the main navigation links to the user list', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('users.index').'"', false)
        ->assertSeeText('사용자 목록');
});

test('guests can see an empty user list', function () {
    $this->get(route('users.index'))
        ->assertOk()
        ->assertSeeText('아직 사용자가 없습니다.');
});

test('the user list links names and total post counts including users without posts', function () {
    $author = User::factory()->has(Post::factory()->count(3))->create(['name' => 'Alice']);
    $otherAuthor = User::factory()->has(Post::factory())->create(['name' => 'Bob']);
    $userWithoutPosts = User::factory()->create(['name' => 'Charlie']);

    $this->get(route('users.index'))
        ->assertOk()
        ->assertSeeTextInOrder(['Alice(3)', 'Bob(1)', 'Charlie(0)'])
        ->assertSee('href="'.route('users.show', $author).'"', false)
        ->assertSee('href="'.route('users.show', $otherAuthor).'"', false)
        ->assertSee('href="'.route('users.show', $userWithoutPosts).'"', false)
        ->assertDontSeeText([$author->email, $otherAuthor->email, $userWithoutPosts->email]);
});

test('the user list paginates in name order', function (int $page, int $offset, int $count) {
    $users = User::factory()->count(16)
        ->sequence(fn (Sequence $sequence): array => ['name' => sprintf('사용자 %02d', 15 - $sequence->index)])
        ->create();
    $expectedUsers = $users->reverse()->slice($offset, $count)->values();

    $this->get(route('users.index', ['page' => $page]))
        ->assertOk()
        ->assertSeeText($expectedUsers->pluck('name')->all())
        ->assertViewHas('users', function (LengthAwarePaginator $paginated) use ($expectedUsers): bool {
            return $paginated->total() === 16
                && $paginated->getCollection()->modelKeys() === $expectedUsers->modelKeys();
        });
})->with([
    'first page' => [1, 0, 15],
    'remaining user' => [2, 15, 1],
]);

test('the user list loads post counts in two queries without N plus one queries', function () {
    $users = User::factory()->count(5)->has(Post::factory()->count(2))->create();
    $labels = $users->map(fn (User $user): string => $user->name.'(2)')->all();
    $this->expectsDatabaseQueryCount(2);

    $this->get(route('users.index'))
        ->assertOk()
        ->assertSeeText($labels);
});

test('guests see only the selected users posts newest first with a stable order', function () {
    $this->freezeTime();
    $user = User::factory()->create();
    $posts = Post::factory()->for($user)->count(3)
        ->sequence(
            ['title' => '오래된 게시글', 'created_at' => now()->subDay()],
            ['title' => '같은 시각의 첫 게시글'],
            ['title' => '같은 시각의 두 번째 게시글'],
        )
        ->create();
    $otherPost = Post::factory()->create(['title' => '다른 사용자의 게시글']);

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertSeeText($user->name.'님의 게시글')
        ->assertSeeTextInOrder(['같은 시각의 두 번째 게시글', '같은 시각의 첫 게시글', '오래된 게시글'])
        ->assertSee('href="'.route('posts.show', $posts->last()).'"', false)
        ->assertSee('href="'.route('users.index').'"', false)
        ->assertDontSeeText($otherPost->title);
});

test('the selected users posts are paginated rather than truncated', function () {
    $this->freezeTime();
    $user = User::factory()->create();
    $posts = Post::factory()->for($user)->count(16)
        ->sequence(fn (Sequence $sequence): array => ['title' => '게시글 '.$sequence->index.' 제목'])
        ->create();

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertViewHas('posts', function (LengthAwarePaginator $paginated) use ($posts): bool {
            return $paginated->total() === 16
                && $paginated->getCollection()->modelKeys() === $posts->reverse()->take(15)->values()->modelKeys();
        })
        ->assertSee('href="'.route('users.show', ['user' => $user, 'page' => 2]).'"', false)
        ->assertDontSeeText($posts->first()->title);
});

test('the next page shows the selected users remaining post', function () {
    $this->freezeTime();
    $user = User::factory()->create();
    $posts = Post::factory()->for($user)->count(16)
        ->sequence(fn (Sequence $sequence): array => ['title' => '게시글 '.$sequence->index.' 제목'])
        ->create();
    $otherPost = Post::factory()->create(['title' => '다른 사용자의 게시글']);

    $this->get(route('users.show', ['user' => $user, 'page' => 2]))
        ->assertOk()
        ->assertSeeText($posts->first()->title)
        ->assertDontSeeText($otherPost->title)
        ->assertViewHas('posts', fn (LengthAwarePaginator $paginated): bool => $paginated->count() === 1);
});

test('users without posts have an empty post list', function () {
    $user = User::factory()->create();
    $otherPost = Post::factory()->create();

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertSeeText([$user->name.'님의 게시글', '아직 작성한 게시글이 없습니다.'])
        ->assertDontSeeText($otherPost->title);
});

test('missing users return not found', function () {
    $this->get(route('users.show', 99999))
        ->assertNotFound();
});

test('user pages escape user names', function (string $routeName) {
    $user = User::factory()->create(['name' => '<img src=x onerror=alert(1)>']);

    $this->get(route($routeName, ['user' => $user]))
        ->assertOk()
        ->assertSee($user->name)
        ->assertDontSee($user->name, false);
})->with(['users.index', 'users.show']);

test('the selected users post list escapes post titles', function () {
    $post = Post::factory()->create(['title' => '<script>alert("title")</script>']);

    $this->get(route('users.show', $post->user))
        ->assertOk()
        ->assertSee($post->title)
        ->assertDontSee($post->title, false);
});

test('the selected users post list uses three queries without N plus one queries', function () {
    $user = User::factory()->has(Post::factory()->count(5))->create();
    $titles = $user->posts()->pluck('title')->all();
    $this->expectsDatabaseQueryCount(3);

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertSeeText($titles);
});
