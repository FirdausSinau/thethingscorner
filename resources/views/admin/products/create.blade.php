@extends('layouts.admin')
@php
    $btnGhost = 'rounded-full border border-border px-4 py-2 text-sm font-medium text-muted transition hover:border-primary/30 hover:text-primary-dark';
@endphp
@section('title', 'Tambah produk · Admin')

@section('content')
    <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-primary hover:text-primary-dark">← Semua produk</a>
    <h1 class="mt-3 font-display text-3xl text-primary-dark">Tambah produk</h1>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.products.store') }}"
          class="mt-6 rounded-2xl border border-border bg-surface p-6">
        @csrf
        @include('admin.products.partials.info-fields', [
            'product' => null,
            'categories' => $categories,
        ])

        <button type="submit"
                class="mt-6 rounded-full bg-primary-dark px-6 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
            Simpan Produk
        </button>
    </form>
@endsection