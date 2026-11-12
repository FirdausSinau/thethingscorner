@extends('layouts.admin')

@section('title', 'Tambah Admin · Thethingscorner')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('admin.admins.index') }}"
           class="text-sm font-medium text-muted transition hover:text-primary-dark">
            &larr; Kembali ke akun admin
        </a>

        <div class="mt-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Akun admin</p>
            <h1 class="mt-2 font-display text-3xl text-primary-dark md:text-4xl">Tambah Admin Biasa</h1>
            <p class="mt-2 text-sm leading-6 text-muted">
                Akun ini bisa mengelola produk, stok, pesanan, dan PO. Akun tidak bisa mengelola admin lain.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.admins.store') }}"
              class="mt-7 rounded-2xl border border-border bg-surface p-6 shadow-sm md:p-8">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-ink">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                           autocomplete="name"
                           @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                           class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                    @error('name')
                        <p id="name-error" class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-ink">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           autocomplete="email"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                           class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                    @error('email')
                        <p id="email-error" class="mt-2 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-medium text-ink">Password</label>
                        <input id="password" name="password" type="password" required
                               autocomplete="new-password"
                               @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                               class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                        <p class="mt-1 text-xs text-muted">Minimal 8 karakter.</p>
                        @error('password')
                            <p id="password-error" class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-ink">Ulangi password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password"
                               class="mt-2 block min-h-11 w-full rounded-xl border border-border bg-bg px-4 py-2.5 text-sm text-ink outline-none transition placeholder:text-muted/60 focus:border-primary/40 focus:ring-2 focus:ring-primary/10">
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-xl bg-bg px-4 py-3 text-xs leading-5 text-muted">
                Role otomatis Admin Biasa. Admin bisa mengganti password lewat link
                &ldquo;Lupa password?&rdquo; di halaman masuk.
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.admins.index') }}"
                   class="inline-flex min-h-11 items-center justify-center rounded-xl border border-border px-5 py-2.5 text-sm font-semibold text-muted transition hover:border-primary/30 hover:text-primary-dark">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                    Simpan admin
                </button>
            </div>
        </form>
    </div>
@endsection

