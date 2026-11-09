@extends('layouts.auth')

@section('title', 'Masuk · The Things Corner')

@section('content')
    <div class="w-full max-w-md rounded-3xl border border-border bg-surface p-7 shadow-sm md:p-9">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-accent">The Things Corner</p>
        <h1 class="mt-3 font-display text-3xl text-primary-dark">Masuk ke akun</h1>
        <p class="mt-2 text-sm leading-6 text-muted">
            Kelola pesanan dan custom PO dari satu dashboard.
        </p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-ink">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       autocomplete="email"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                       class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                @error('email')
                    <p id="email-error" class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between gap-3">
                    <label for="password" class="block text-sm font-medium text-ink">Password</label>
                    <a href="{{ route('password.request') }}"
                       class="text-xs font-semibold text-primary transition hover:text-primary-dark">
                        Lupa password?
                    </a>
                </div>
                <input id="password" name="password" type="password" required
                       autocomplete="current-password"
                       @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                       class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                @error('password')
                    <p id="password-error" class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" name="remember" value="1"
                       class="h-4 w-4 rounded border-border text-primary focus:ring-primary/20">
                Ingat saya
            </label>

            <button type="submit"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
                Masuk
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-muted">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-primary transition hover:text-primary-dark">
                Daftar
            </a>
        </p>
    </div>
@endsection
