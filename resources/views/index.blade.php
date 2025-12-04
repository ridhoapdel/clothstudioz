@extends('layouts.app')

@section('title', 'Home Page')

@section('styles')
<style>
    .product-card {
        transition: all 0.3s ease;
        z-index: 10;
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
<!-- Slider -->
<div class="relative w-full overflow-hidden mt-11 top-5">
    <div class="slider-container flex transition-transform duration-700">
        <img src="{{ asset('aset/d.jpg') }}" alt="Slide 1" class="w-full flex-shrink-0 object-cover" style="max-height: 700px;">
        <img src="{{ asset('aset/s.jpg') }}" alt="Slide 2" class="w-full flex-shrink-0 object-cover" style="max-height: 700px;">
        <img src="{{ asset('aset/a.jpg') }}" alt="Slide 3" class="w-full flex-shrink-0 object-cover" style="max-height: 700px;">
    </div>

    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white z-10 px-4">
        <h1 class="text-xl md:text-3xl font-bold mb-4 text-shadow" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5), 0 0 25px rgba(255, 255, 255, 0.5);">WELCOME TO LAVIADE<br>NEW PRODUCT OUT NOW</h1>

        <div class="flex flex-row justify-center space-x-4 mt-4 overflow-x-auto">
            <a href="{{ url('/shopAll') }}" class="min-w-32 h-10 bg-white bg-opacity-10 backdrop-filter backdrop-blur-md text-white border border-white px-4 py-2 rounded-sm transition duration-300 hover:bg-white hover:text-black hover:border-black text-sm md:text-base">
                SHOP NOW
            </a>
            <a href="{{ url('/categories') }}" class="min-w-32 h-10 bg-white bg-opacity-10 backdrop-filter backdrop-blur-md text-white border border-white px-4 py-2 rounded-sm transition duration-300 hover:bg-white hover:text-black hover:border-black text-sm md:text-base">
                CATEGORIES
            </a>
        </div>
    </div>

    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
        <button class="slide-btn w-3 h-3 rounded-full bg-white"></button>
        <button class="slide-btn w-3 h-3 rounded-full bg-gray-400"></button>
        <button class="slide-btn w-3 h-3 rounded-full bg-gray-400"></button>
    </div>
</div>

<!-- NEW PRODUCTS SECTION -->
<h1 class="text-lg font-bold mb-4 mt-14 text-center">NEW PRODUCTS</h1>
<div class="grid grid-cols-5 md:grid-cols-5 lg:grid-cols-4 gap-6 py-10">
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
function addToCart(produkId) {
    fetch('{{ url("/add_to_cart") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: 'produk_id=' + produkId
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Produk ditambahkan ke keranjang!');
            const cartCount = document.getElementById('cart-count');
            if(cartCount) cartCount.innerText = parseInt(cartCount.innerText) + 1;
        } else {
            alert(data.message);
        }
    });
}
</script>
<script src="{{ asset('js/home.js') }}"></script>
@endsection
