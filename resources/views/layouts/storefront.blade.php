<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Thethingscorner')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col bg-bg text-ink antialiased">
    <header class="sticky top-0 z-20 border-b border-border bg-surface/95 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-5 py-4 md:px-8">
            <a href="{{ route('home') }}" class="group inline-flex items-baseline gap-2">
                <span class="font-display text-xl font-semibold tracking-tight text-primary-dark">Thethingscorner</span>
                <span class="hidden text-[10px] uppercase tracking-[0.25em] text-muted sm:inline">Bandung</span>
            </a>

            <div class="flex items-center gap-2 text-sm sm:gap-3">
                <a href="{{ route('catalog.index') }}"
                   class="rounded-full px-3 py-2 font-medium text-primary-dark transition hover:bg-primary/5">
                    Katalog
                </a>

                @auth
                     <a href="{{ route('cart.index') }}"
                       class="rounded-full px-3 py-2 font-medium text-primary-dark transition hover:bg-primary/5">
                        Keranjang
                    </a>
                    <!-- <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                       class="hidden rounded-full px-3 py-2 font-medium text-primary-dark transition hover:bg-primary/5 sm:inline-flex">
                        Dashboard
                    </a> -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-full border border-border px-4 py-2 font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="rounded-full px-3 py-2 font-medium text-primary-dark transition hover:bg-primary/5">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                       class="rounded-full bg-primary px-4 py-2 font-medium text-white transition hover:bg-primary-dark">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        @if (session('status'))
            <div class="mx-auto mt-4 w-full max-w-6xl px-5 md:px-8">
                <div class="rounded-xl border border-success/20 bg-success/5 px-4 py-3 text-sm text-success">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-border bg-surface">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-2 px-5 py-8 text-xs text-muted md:flex-row md:items-center md:justify-between md:px-8">
            <p>Handmade custom shoes · Bandung</p>
            <p>&copy; {{ date('Y') }} Thethingscorner</p>
        </div>
    </footer>
</body>
</html>

