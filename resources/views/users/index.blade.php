<x-layout title="사용자 목록">
    <section class="flex flex-col gap-6">
        <h1 class="text-2xl font-semibold">사용자 목록</h1>
        <ul class="flex flex-col gap-4">
            @forelse ($users as $user)
                <li class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <a href="{{ route('users.show', $user) }}" class="wrap-anywhere text-lg font-semibold hover:underline">{{ $user->name }}({{ $user->posts_count }})</a>
                </li>
            @empty
                <li class="text-gray-600 dark:text-gray-400">아직 사용자가 없습니다.</li>
            @endforelse
        </ul>
        {{ $users->links() }}
    </section>
</x-layout>
