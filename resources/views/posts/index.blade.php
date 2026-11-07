<x-layout title="게시글 목록">
    <section class="flex flex-col gap-6">
        <h1 class="text-2xl font-semibold">게시글 목록</h1>
        <div class="flex flex-col gap-4">
            @forelse ($posts as $post)
                <article class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="wrap-anywhere text-lg font-semibold">
                        <a href="{{ route('posts.show', $post) }}" class="hover:underline">{{ $post->title }}</a>
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $post->user->name }}
                        <time datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->format('Y-m-d H:i') }}</time>
                    </p>
                </article>
            @empty
                <p class="text-gray-600 dark:text-gray-400">아직 게시글이 없습니다. 첫 게시글을 작성해 보세요.</p>
            @endforelse
        </div>
        {{ $posts->links() }}
    </section>
</x-layout>
