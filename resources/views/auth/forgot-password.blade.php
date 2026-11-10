@extends('layouts.auth')

@section('title', 'Lupa Password · Thethingscorner')

@section('content')
    <div class="w-full rounded-3xl border border-border bg-surface p-7 shadow-sm md:p-9">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-accent">The Things Corner</p>
        <h1 class="mt-3 font-display text-3xl text-primary-dark">Lupa password</h1>
        <p class="mt-2 text-sm leading-6 text-muted">
            Masukkan email akun kamu. Kami akan mengirim link untuk membuat password baru.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
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

            <button type="submit"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
                Kirim link reset
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-muted">
            Ingat password?
            <a href="{{ route('login') }}" class="font-semibold text-primary transition hover:text-primary-dark">
                Masuk
            </a>
        </p>
    </div>
@endsection
