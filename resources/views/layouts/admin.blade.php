<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin · Thethingscorner')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-bg text-ink antialiased">
    <header class="border-b border-border bg-surface">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-5 py-4 md:px-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="font-display text-lg font-semibold text-primary-dark">
                    Thethingscorner
                </a>
                <span class="rounded-full bg-primary/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary">
                    Admin
                </span>
            </div>

            <div class="flex items-center gap-3 text-sm">
                <div class="hidden text-right sm:block">
                    <p class="font-medium text-ink">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-muted">{{ auth()->user()->role->label() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-full border border-border px-4 py-2 font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <nav class="border-b border-border bg-surface md:hidden">
        <div class="mx-auto flex w-full max-w-7xl items-center gap-1 overflow-x-auto px-5 py-2 text-sm">
            <a href="{{ route('admin.dashboard') }}"
               class="shrink-0 rounded-full px-3 py-2 font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary-dark' : 'text-muted hover:bg-primary/5 hover:text-primary-dark' }}">
                Dashboard
            </a>

            <a href="{{ route('admin.products.index') }}"
               class="shrink-0 rounded-full px-3 py-2 font-medium transition {{ request()->routeIs('admin.products.*') ? 'bg-primary/10 text-primary-dark' : 'text-muted hover:bg-primary/5 hover:text-primary-dark' }}">
                Produk
            </a>

            <a href="{{ route('admin.orders.index') }}"
               class="shrink-0 rounded-full px-3 py-2 font-medium transition {{ request()->routeIs('admin.orders.*') ? 'bg-primary/10 text-primary-dark' : 'text-muted hover:bg-primary/5 hover:text-primary-dark' }}">
                Pesanan
            </a>

            @if (auth()->user()->isAdminMaster())
                <a href="{{ route('admin.admins.index') }}"
                   class="shrink-0 rounded-full px-3 py-2 font-medium transition {{ request()->routeIs('admin.admins.*') ? 'bg-primary/10 text-primary-dark' : 'text-muted hover:bg-primary/5 hover:text-primary-dark' }}">
                    Akun admin
                </a>
            @endif
        </div>
    </nav>

    <div class="mx-auto flex w-full max-w-7xl gap-8 px-5 py-8 md:px-8">
        <aside class="hidden w-56 shrink-0 md:block">
            <nav class="space-y-1 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center justify-between rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary/5 font-medium text-primary-dark' : 'text-muted hover:bg-primary/5 hover:text-primary-dark' }}"
                   @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    Dashboard
                </a>

                @if (auth()->user()->isAdminMaster())
                    <a href="{{ route('admin.admins.index') }}"
                       class="flex items-center justify-between rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.admins.*') ? 'bg-primary/5 font-medium text-primary-dark' : 'text-muted hover:bg-primary/5 hover:text-primary-dark' }}"
                       @if (request()->routeIs('admin.admins.*')) aria-current="page" @endif>
                        Akun admin
                    </a>
                @endif

                <a href="{{ route('admin.products.index') }}"
                    class="flex items-center justify-between rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.products.*') ? 'bg-primary/5 font-medium text-primary-dark' : 'text-muted hover:bg-primary/5 hover:text-primary-dark' }}">
                    Produk &amp; stok
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="flex items-center justify-between rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.orders.*') ? 'bg-primary/5 font-medium text-primary-dark' : 'text-muted hover:bg-primary/5 hover:text-primary-dark' }}"
                   @if (request()->routeIs('admin.orders.*')) aria-current="page" @endif>
                    Pesanan
                </a>

                @foreach (['Custom PO', 'Review', 'Laporan'] as $item)
                    <span class="flex items-center justify-between rounded-xl px-3 py-2.5 text-muted">
                        {{ $item }}
                        <span class="text-[10px] uppercase tracking-wide text-muted/70">segera</span>
                    </span>
                @endforeach
            </nav>
        </aside>

        <main class="min-w-0 flex-1">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-success/20 bg-success/5 px-4 py-3 text-sm text-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-danger/20 bg-danger/5 px-4 py-3 text-sm text-danger">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

    </div>
</body>
</html>

