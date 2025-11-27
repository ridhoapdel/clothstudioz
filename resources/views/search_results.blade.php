@extends('layouts.app')

@section('title', 'Search Results')

@section('styles')
<style>
    .product-card {
        transition: all 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .price-cut {
        text-decoration: line-through;
        color: #999;
    }
    .discount-badge {
        background-color: #ef4444;
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 mt-20 py-8">
    <h1 class="text-2xl font-bold mb-6">Hasil Pencarian: "{{ $query }}"</h1>
    
    @if(count($products) > 0)
        <p class="text-gray-600 mb-6">Ditemukan {{ count($products) }} produk</p>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                <a href="{{ url('/product/' . $product->produk_id) }}" class="product-card bg-white p-4 rounded-md shadow-md block relative">
                    <!-- Sold Out Label -->
                    @if($product->stok <= 0)
                        <span class="sold-out">SOLD OUT</span>
                    @endif
                    
                    <!-- Discount Badge -->
                    @if(!empty($product->diskon_persen))
                        <span class="discount-badge">-{{ $product->diskon_persen }}%</span>
                    @endif
                    
                    <!-- Product Image -->
                    <img src="{{ asset('uploads/' . $product->gambar_produk) }}" 
                         alt="{{ $product->nama_produk }}" 
                         class="w-full aspect-square object-cover mb-3 rounded">
                    
                    <!-- Product Info -->
                    <h3 class="text-sm font-medium">{{ $product->nama_produk }}</h3>
                    
                    <!-- Price -->
                    @if(!empty($product->harga_diskon) && $product->harga_diskon < $product->harga)
                        <p class="text-xs price-cut">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                        <p class="text-sm font-bold text-black">Rp {{ number_format($product->harga_diskon, 0, ',', '.') }}</p>
                    @else
                        <p class="text-sm font-bold">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <i class="fa fa-search text-5xl text-gray-300 mb-4"></i>
            <p class="text-xl text-gray-600 mb-4">Tidak ada produk yang ditemukan untuk "{{ $query }}"</p>
            <a href="{{ url('/shopAll') }}" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
                Lihat Semua Produk
            </a>
        </div>
    @endif
</div>
@endsection
