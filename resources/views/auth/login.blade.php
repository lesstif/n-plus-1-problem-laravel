<x-layout title="로그인">
    <section class="mx-auto flex max-w-md flex-col gap-6">
        <h1 class="text-2xl font-semibold">로그인</h1>
        <p class="text-gray-600 dark:text-gray-400">로그인한 사용자만 게시글을 작성할 수 있습니다.</p>
        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf
            <div class="flex flex-col gap-2">
                <label for="email" class="font-medium">이메일</label>
                <input
                    id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="username"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:outline-2 focus:outline-blue-600 dark:border-gray-700 dark:bg-gray-900"
                >
            </div>
            <div class="flex flex-col gap-2">
                <label for="password" class="font-medium">비밀번호</label>
                <input
                    id="password" name="password" type="password" required autocomplete="current-password"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 focus:outline-2 focus:outline-blue-600 dark:border-gray-700 dark:bg-gray-900"
                >
            </div>
            <button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">로그인</button>
        </form>
    </section>
</x-layout>
