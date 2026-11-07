<x-layout :title="$user->name.'님의 게시글'">
    <section class="flex flex-col gap-6">
        <h1 class="wrap-anywhere text-2xl font-semibold">{{ $user->name }}님의 게시글</h1>
        <a href="{{ route('users.index') }}" class="underline underline-offset-4">사용자 목록으로</a>
        <div class="flex flex-col gap-4">
            @forelse ($posts as $post)
                <article class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="wrap-anywhere text-lg font-semibold">
                        <a href="{{ route('posts.show', $post) }}" class="hover:underline">{{ $post->title }}</a>
                    </h2>
                    <time datetime="{{ $post->created_at->toIso8601String() }}" class="text-sm text-gray-600 dark:text-gray-400">{{ $post->created_at->format('Y-m-d H:i') }}</time>
                </article>
            @empty
                <p class="text-gray-600 dark:text-gray-400">아직 작성한 게시글이 없습니다.</p>
            @endforelse
        </div>
        {{ $posts->links() }}
    </section>
</x-layout>
