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
    .filter-checkbox:checked + label {
        background-color: #000;
        color: white;
    }
    .sold-out {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(0,0,0,0.7);
        color: white;
        padding: 0.5rem 1rem;
        font-weight: bold;
        z-index: 10;
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 mt-20 py-10">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Filters Sidebar -->
        <aside class="lg:w-64 shrink-0">
            <div class="bg-white p-4 rounded-lg shadow sticky top-20">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Filter</h2>
                    <button id="clearFilters" class="text-sm text-red-500 hover:text-red-700">Clear All</button>
                </div>
                
                <!-- Category Filter -->
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Kategori</h3>
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="filter-checkbox mr-2" name="kategori" value="Pria">
                            <span>Pria</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="filter-checkbox mr-2" name="kategori" value="Wanita">
                            <span>Wanita</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="filter-checkbox mr-2" name="kategori" value="Anak">
                            <span>Anak</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="filter-checkbox mr-2" name="kategori" value="Aksesoris">
                            <span>Aksesoris</span>
                        </label>
                    </div>
                </div>
                
                <!-- Price Range Filter -->
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Harga</h3>
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="filter-checkbox mr-2" name="price" value="0-100000">
                            <span>< Rp 100.000</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="filter-checkbox mr-2" name="price" value="100000-200000">
                            <span>Rp 100.000 - Rp 200.000</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="filter-checkbox mr-2" name="price" value="200000-300000">
                            <span>Rp 200.000 - Rp 300.000</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="filter-checkbox mr-2" name="price" value="300000-10000000">
                            <span>> Rp 300.000</span>
                        </label>
                    </div>
                </div>
                
                <!-- Size Filter -->
                <div class="mb-6">
                    <h3 class="font-semibold mb-2">Ukuran</h3>
                    <div class="flex flex-wrap gap-2">
                        <label class="cursor-pointer">
                            <input type="checkbox" class="filter-checkbox hidden" name="size" value="S">
                            <span class="inline-block px-3 py-1 border rounded hover:bg-gray-100">S</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" class="filter-checkbox hidden" name="size" value="M">
                            <span class="inline-block px-3 py-1 border rounded hover:bg-gray-100">M</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" class="filter-checkbox hidden" name="size" value="L">
                            <span class="inline-block px-3 py-1 border rounded hover:bg-gray-100">L</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" class="filter-checkbox hidden" name="size" value="XL">
                            <span class="inline-block px-3 py-1 border rounded hover:bg-gray-100">XL</span>
                        </label>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Products Grid -->
        <main class="flex-1">
            <!-- Sort & Count -->
            <div class="flex justify-between items-center mb-4">
                <p class="text-gray-600"><span id="productCount">{{ count($products) }}</span> Produk</p>
                <div class="relative">
                    <button id="sortButton" class="flex items-center gap-2 px-4 py-2 border rounded hover:bg-gray-50">
                        <span>Urutkan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="sortMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg z-10">
                        <button class="sort-option w-full text-left px-4 py-2 hover:bg-gray-100" data-sort="newest">Terbaru</button>
                        <button class="sort-option w-full text-left px-4 py-2 hover:bg-gray-100" data-sort="price-low">Harga Terendah</button>
                        <button class="sort-option w-full text-left px-4 py-2 hover:bg-gray-100" data-sort="price-high">Harga Tertinggi</button>
                        <button class="sort-option w-full text-left px-4 py-2 hover:bg-gray-100" data-sort="name">Nama A-Z</button>
                    </div>
                </div>
            </div>
            
            <!-- Products -->
            <div id="productsContainer" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    @php
                        $in_wishlist = in_array($product->produk_id, $wishlistProductIds ?? []);
                    @endphp
                    <div class="product-card bg-white p-4 rounded-md shadow-md relative"
                       data-kategori="{{ $product->kategori ?? '' }}"
                       data-price="{{ $product->harga_diskon ?? $product->harga }}"
                       data-name="{{ $product->nama_produk }}"
                       data-created="{{ $product->created_at ?? '' }}">
                        <!-- Sold Out Label -->
                        @if($product->stok <= 0)
                            <span class="sold-out">SOLD OUT</span>
                        @endif
                        
                        <!-- Discount Badge -->
                        @if(!empty($product->diskon_persen))
                            <span class="discount-badge">-{{ $product->diskon_persen }}%</span>
                        @endif
                        
                        <!-- Wishlist Button -->
                        @if(session()->has('user_id'))
                            <button onclick="toggleWishlistCard({{ $product->produk_id }}, this); event.preventDefault(); event.stopPropagation();" 
                                    class="absolute top-2 left-2 z-10 p-2 bg-white rounded-full shadow hover:bg-gray-100 transition">
                                <i class="fa fa-heart {{ $in_wishlist ? 'text-pink-500' : 'text-gray-400' }}"></i>
                            </button>
                        @endif
                        
                        <!-- Product Image -->
                        <a href="{{ url('/product/' . $product->produk_id) }}">
                            <img src="{{ asset('uploads/' . $product->gambar_produk) }}" 
                                 alt="{{ $product->nama_produk }}" 
                                 class="w-full aspect-square object-cover mb-3 rounded">
                        </a>
                        
                        <!-- Product Info -->
                        <a href="{{ url('/product/' . $product->produk_id) }}">
                            <h3 class="text-sm font-medium">{{ $product->nama_produk }}</h3>
                            
                            <!-- Price -->
                            @if(!empty($product->harga_diskon) && $product->harga_diskon < $product->harga)
                                <p class="text-xs price-cut">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                                <p class="text-sm font-bold text-black">Rp {{ number_format($product->harga_diskon, 0, ',', '.') }}</p>
                            @else
                                <p class="text-sm font-bold">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
            
            <!-- No Results -->
            <div id="noResults" class="hidden text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-4 text-lg text-gray-600">Tidak ada produk yang sesuai dengan filter</p>
                <button id="resetFiltersBtn" class="mt-4 px-6 py-2 bg-black text-white rounded hover:bg-gray-800">Reset Filter</button>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
    const sortButton = document.getElementById('sortButton');
    const sortMenu = document.getElementById('sortMenu');
    const sortOptions = document.querySelectorAll('.sort-option');
    const clearFilters = document.getElementById('clearFilters');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const productsContainer = document.getElementById('productsContainer');
    const noResults = document.getElementById('noResults');
    const productCount = document.getElementById('productCount');
    
    let currentSort = 'newest';
    
    // Toggle sort menu
    if (sortButton) {
        sortButton.addEventListener('click', function() {
            sortMenu.classList.toggle('hidden');
        });
        
        // Close menu when clicking outside
        window.addEventListener('click', function(e) {
            if (!sortMenu.contains(e.target) && !sortButton.contains(e.target)) {
                sortMenu.classList.add('hidden');
            }
        });
    }
    
    // Apply filters
    function applyFilters() {
        const selectedCategories = Array.from(document.querySelectorAll('input[name="kategori"]:checked')).map(cb => cb.value);
        const selectedPrices = Array.from(document.querySelectorAll('input[name="price"]:checked')).map(cb => cb.value);
        const selectedSizes = Array.from(document.querySelectorAll('input[name="size"]:checked')).map(cb => cb.value);
        
        let visibleCount = 0;
        
        productCards.forEach(card => {
            let show = true;
            
            // Filter by category
            if (selectedCategories.length > 0) {
                const cardCategory = card.dataset.kategori;
                if (!selectedCategories.includes(cardCategory)) {
                    show = false;
                }
            }
            
            // Filter by price
            if (selectedPrices.length > 0 && show) {
                const cardPrice = parseInt(card.dataset.price);
                let matchesPrice = false;
                
                selectedPrices.forEach(range => {
                    const [min, max] = range.split('-').map(Number);
                    if (cardPrice >= min && cardPrice <= max) {
                        matchesPrice = true;
                    }
                });
                
                if (!matchesPrice) {
                    show = false;
                }
            }
            
            // Show/hide card
            if (show) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update count
        productCount.textContent = visibleCount;
        
        // Show no results message
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
            productsContainer.classList.add('hidden');
        } else {
            noResults.classList.add('hidden');
            productsContainer.classList.remove('hidden');
        }
        
        // Apply current sort
        sortProducts(currentSort);
    }
    
    // Sort products
    function sortProducts(sortType) {
        currentSort = sortType;
        const cardsArray = Array.from(productCards);
        
        cardsArray.sort((a, b) => {
            switch(sortType) {
                case 'price-low':
                    return parseInt(a.dataset.price) - parseInt(b.dataset.price);
                case 'price-high':
                    return parseInt(b.dataset.price) - parseInt(a.dataset.price);
                case 'name':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'newest':
                default:
                    return new Date(b.dataset.created) - new Date(a.dataset.created);
            }
        });
        
        // Reorder DOM elements
        cardsArray.forEach(card => {
            productsContainer.appendChild(card);
        });
    }
    
    // Event listeners
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });
    
    sortOptions.forEach(option => {
        option.addEventListener('click', function() {
            sortProducts(this.dataset.sort);
            sortMenu.classList.add('hidden');
        });
    });
    
    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            filterCheckboxes.forEach(cb => cb.checked = false);
            applyFilters();
        });
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            filterCheckboxes.forEach(cb => cb.checked = false);
            applyFilters();
        });
    }
});

// Wishlist toggle for product cards
function toggleWishlistCard(productId, button) {
    @if(!session()->has('user_id'))
        alert('Silakan login terlebih dahulu');
        window.location.href = '{{ url("/users/login") }}';
        return;
    @endif
    
    const icon = button.querySelector('i');
    const isInWishlist = icon.classList.contains('text-pink-500');
    
    const url = isInWishlist ? '{{ url("/remove_from_wishlist") }}' : '{{ url("/add_to_wishlist") }}';
    const formData = new FormData();
    formData.append('produk_id', productId);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isInWishlist) {
                icon.classList.remove('text-pink-500');
                icon.classList.add('text-gray-400');
            } else {
                icon.classList.remove('text-gray-400');
                icon.classList.add('text-pink-500');
            }
            // Show notification
            showNotification(isInWishlist ? 'Dihapus dari wishlist' : 'Ditambahkan ke wishlist');
        } else {
            alert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses wishlist');
    });
}

// Simple notification function
function showNotification(message) {
    const notif = document.createElement('div');
    notif.className = 'fixed bottom-4 right-4 bg-black text-white px-6 py-3 rounded shadow-lg z-50';
    notif.textContent = message;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.remove();
    }, 3000);
}
</script>
@endsection
