<x-layout title="게시글 작성">
    <section class="flex flex-col gap-6">
        <h1 class="text-2xl font-semibold">게시글 작성</h1>
        <form method="POST" action="{{ route('posts.store') }}" class="flex flex-col gap-6">
            @csrf
            <div class="flex flex-col gap-2">
                <label for="title" class="font-medium">제목</label>
                <input
                    id="title" name="title" type="text" value="{{ old('title') }}" required maxlength="255"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:outline-2 focus:outline-blue-600 dark:border-gray-700 dark:bg-gray-900"
                >
                <p class="text-sm text-gray-600 dark:text-gray-400">최대 255자</p>
            </div>
            <div class="flex flex-col gap-2">
                <label for="body" class="font-medium">내용</label>
                <textarea
                    id="body" name="body" rows="12" required maxlength="10000"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:outline-2 focus:outline-blue-600 dark:border-gray-700 dark:bg-gray-900"
                >{{ old('body') }}</textarea>
                <p class="text-sm text-gray-600 dark:text-gray-400">최대 10,000자, HTML은 일반 텍스트로 표시됩니다.</p>
            </div>
            <div class="flex items-center gap-4">
                <button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">등록</button>
                <a href="{{ route('posts.index') }}" class="underline underline-offset-4">취소</a>
            </div>
        </form>
    </section>
</x-layout>
