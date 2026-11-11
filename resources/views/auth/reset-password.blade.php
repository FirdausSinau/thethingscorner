@extends('layouts.auth')

@section('title', 'Reset Password · thethingscorner')

@section('content')
    <div class="w-full rounded-3xl border border-border bg-surface p-7 shadow-sm md:p-9">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-accent">thethingscorner</p>
        <h1 class="mt-3 font-display text-3xl text-primary-dark">Buat password baru</h1>
        <p class="mt-2 text-sm leading-6 text-muted">
            Pilih password baru untuk akun kamu. Minimal 8 karakter.
        </p>

        <form method="POST" action="{{ route('password.store') }}" class="mt-7 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-medium text-ink">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required
                       autocomplete="email"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                       class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                @error('email')
                    <p id="email-error" class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-ink">Password baru</label>
                <input id="password" name="password" type="password" required autofocus
                       autocomplete="new-password"
                       @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                       class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                @error('password')
                    <p id="password-error" class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-ink">Ulangi password baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       autocomplete="new-password"
                       class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
            </div>

            <button type="submit"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
                Simpan password baru
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-muted">
            <a href="{{ route('login') }}" class="font-semibold text-primary transition hover:text-primary-dark">
                Kembali ke halaman masuk
            </a>
        </p>
    </div>
@endsection

