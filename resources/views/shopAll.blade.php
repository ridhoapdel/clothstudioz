@extends('layouts.app')

@section('title', 'Shop All')

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
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-20 py-10">
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
@endsection

@section('scripts')
<script>
    // Toggle dropdown menu
    document.getElementById('sortButton').addEventListener('click', function () {
        const menu = document.getElementById('sortMenu');
        menu.classList.toggle('hidden');
    });

    // Close menu when clicking outside
    window.addEventListener('click', function (e) {
        const menu = document.getElementById('sortMenu');
        const button = document.getElementById('sortButton');
        if (!menu.contains(e.target) && !button.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
</script>
@endsection
