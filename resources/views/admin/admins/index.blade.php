@extends('layouts.admin')

@section('title', 'Akun Admin · Thethingscorner')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-muted">Pengaturan</p>
            <h1 class="mt-2 font-display text-3xl text-primary-dark md:text-4xl">Akun admin</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">
                Buat dan nonaktifkan Admin Biasa. Akun Admin Master hanya dibuat lewat seeder.
            </p>
        </div>

        <a href="{{ route('admin.admins.create') }}"
           class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
            Tambah Admin Biasa
        </a>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-border bg-surface shadow-sm">
        <div class="divide-y divide-border md:hidden">
            @forelse ($admins as $admin)
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-ink">{{ $admin->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-muted">{{ $admin->email }}</p>
                            @if ($admin->is(auth()->user()))
                                <p class="mt-1 text-xs text-muted">Akun kamu</p>
                            @endif
                        </div>

                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $admin->is_active ? 'bg-success/10 text-success' : 'bg-muted/10 text-muted' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $admin->is_active ? 'bg-success' : 'bg-muted' }}"></span>
                            {{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $admin->isAdminMaster() ? 'bg-primary/10 text-primary' : 'border border-border bg-bg text-muted' }}">
                            {{ $admin->role->label() }}
                        </span>

                        @if ($admin->isAdminMaster())
                            <span class="text-xs text-muted">Dilindungi</span>
                        @else
                            <form method="POST"
                                  action="{{ route($admin->is_active ? 'admin.admins.deactivate' : 'admin.admins.activate', $admin) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        @if ($admin->is_active) onclick="return confirm('Nonaktifkan akun ini? Admin tidak bisa masuk sampai diaktifkan kembali.')" @endif
                                        class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $admin->is_active ? 'border-danger/20 text-danger hover:bg-danger/5' : 'border-primary/20 text-primary hover:bg-primary/5' }}">
                                    {{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="px-5 py-12 text-center text-sm text-muted">Belum ada akun admin.</p>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="w-full divide-y divide-border text-sm">
                <thead class="bg-bg/60 text-left text-xs uppercase tracking-[0.14em] text-muted">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Email</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Role</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($admins as $admin)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-medium text-ink">{{ $admin->name }}</p>
                                @if ($admin->is(auth()->user()))
                                    <p class="mt-0.5 text-xs text-muted">Akun kamu</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-muted">{{ $admin->email }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $admin->isAdminMaster() ? 'bg-primary/10 text-primary' : 'border border-border bg-bg text-muted' }}">
                                    {{ $admin->role->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $admin->is_active ? 'bg-success/10 text-success' : 'bg-muted/10 text-muted' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $admin->is_active ? 'bg-success' : 'bg-muted' }}"></span>
                                    {{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if ($admin->isAdminMaster())
                                    <span class="text-xs text-muted">Dilindungi</span>
                                @else
                                    <form method="POST"
                                          action="{{ route($admin->is_active ? 'admin.admins.deactivate' : 'admin.admins.activate', $admin) }}"
                                          class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                @if ($admin->is_active) onclick="return confirm('Nonaktifkan akun ini? Admin tidak bisa masuk sampai diaktifkan kembali.')" @endif
                                                class="whitespace-nowrap rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $admin->is_active ? 'border-danger/20 text-danger hover:bg-danger/5' : 'border-primary/20 text-primary hover:bg-primary/5' }}">
                                            {{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-muted">
                                Belum ada akun admin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($admins->hasPages())
        <div class="mt-4">
            {{ $admins->links() }}
        </div>
    @endif
@endsection

