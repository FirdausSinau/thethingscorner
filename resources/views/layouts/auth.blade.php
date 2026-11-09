<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'The Things Corner')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-bg text-ink antialiased">
    <div class="flex min-h-dvh flex-col">
        <header class="border-b border-border bg-surface">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-4 md:px-8">
                <a href="{{ route('home') }}" class="font-display text-lg font-semibold text-primary-dark">
                    The Things Corner
                </a>
                <a href="{{ route('home') }}" class="text-sm font-medium text-muted transition hover:text-primary-dark">
                    Kembali ke toko
                </a>
            </div>
        </header>

        <main class="flex flex-1 items-center justify-center px-5 py-12">
            <div class="w-full max-w-md">
                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-success/20 bg-success/5 px-4 py-3 text-sm text-success">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="px-5 pb-8 text-center text-xs text-muted">
            Handmade custom shoes · Bandung
        </footer>
    </div>
</body>
</html>
