<x-layout :title="$post->title">
    <article class="flex flex-col gap-6">
        <h1 class="wrap-anywhere text-2xl font-semibold">{{ $post->title }}</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $post->user->name }}
            <time datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->format('Y-m-d H:i') }}</time>
        </p>
        <div class="wrap-anywhere whitespace-pre-wrap leading-7">{{ $post->body }}</div>
        <a href="{{ route('posts.index') }}" class="underline underline-offset-4">목록으로</a>
    </article>
</x-layout>
