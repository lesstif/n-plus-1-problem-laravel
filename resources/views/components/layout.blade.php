@props(['title' => '게시글'])

<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
        <div class="mx-auto flex max-w-4xl flex-col gap-8 px-4 py-8 sm:px-6">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 pb-6 dark:border-gray-800">
                <a href="{{ route('posts.index') }}" class="text-xl font-semibold">게시판</a>
                <nav aria-label="주 메뉴" class="flex flex-wrap items-center gap-4 text-sm">
                    <a href="{{ route('posts.create') }}" class="underline underline-offset-4">글쓰기</a>
                    @auth
                        <span>{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="cursor-pointer underline underline-offset-4">로그아웃</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="underline underline-offset-4">로그인</a>
                    @endauth
                </nav>
            </header>

            @if (session('status'))
                <p role="status" class="rounded-lg bg-green-50 p-4 text-green-800 dark:bg-green-950 dark:text-green-200">
                    {{ session('status') }}
                </p>
            @endif

            @if ($errors->any())
                <div role="alert" class="rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-950 dark:text-red-200">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <main>{{ $slot }}</main>
        </div>
    </body>
</html>
