@extends('layouts.admin')

@section('title', 'Kelola ' . $product->name . ' · Admin')

@section('content')
    @include('admin.products.partials.header', ['product' => $product])
    @include('admin.products.partials.info-form', ['product' => $product, 'categories' => $categories])
    @include('admin.products.partials.product-images', ['product' => $product])
    @include('admin.products.partials.colors', ['product' => $product])
    @include('admin.products.partials.deactivate', ['product' => $product])
@endsection
