@extends('layouts.app')

@section('title', $product->nama_produk . ' - Laviade')

@section('styles')
<style>
    .slide-in {
        animation: slide-in 0.5s forwards;
    }
    @keyframes slide-in {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
    .wishlist-icon {
        transition: all 0.3s ease;
    }
    .wishlist-icon.active {
        color: #ec4899;
        transform: scale(1.1);
    }
    .star-rating {
        color: #facc15;
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        pointer-events: none;
    }
    .modal.show {
        display: flex;
        pointer-events: auto;
    }
    .modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: 0.5rem;
        width: 90%;
        max-width: 500px;
        pointer-events: auto;
    }
    .size-btn {
        pointer-events: auto !important;
        z-index: 10 !important;
        position: relative !important;
        cursor: pointer !important;
        user-select: none !important;
    }
    
    /* Ensure no overlays block clicks */
    .size-button:hover,
    #addToCartButton:hover,
    #wishlistButton:hover,
    button[onclick*="buyNow"]:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    
    /* Ensure no overlays block clicks */
    main, section {
        position: relative;
        z-index: 1;
    }
    
    /* Fix navbar z-index issues */
    #navbar {
        z-index: 40 !important;
    }
    
    /* Ensure buttons are above other content */
    .flex.items-center.space-x-4 {
        position: relative;
        z-index: 5;
        pointer-events: auto !important;
    }
    
    /* Remove any potential overlays */
    .container {
        position: relative;
        z-index: 1;
        pointer-events: auto;
    }
    
    /* Ensure action buttons are clickable */
    #addToCartButton, #wishlistButton, button[onclick*="buyNow"] {
        pointer-events: auto !important;
        cursor: pointer !important;
        user-select: none !important;
    }
    
    #addToCartButton:disabled, button[onclick*="buyNow"]:disabled {
        cursor: not-allowed !important;
        opacity: 0.6 !important;
    }
    
    /* Debug styling for size buttons */
    .size-btn {
        border: 2px solid #000 !important;
        background: #fff !important;
        color: #000 !important;
        font-weight: bold !important;
        min-width: 50px !important;
        height: 45px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.2s ease !important;
    }
    
    .size-btn:hover {
        background: #000 !important;
        color: #fff !important;
        transform: scale(1.05) !important;
    }
    
    .size-btn.selected {
        background: #000 !important;
        color: #fff !important;
    }
</style>
@endsection

@section('content')
<!-- Sidebar Notifikasi Cart -->
<div id="notifSidebar" class="fixed right-0 top-0 h-full w-1/3 bg-white shadow-lg p-6 transform translate-x-full transition-transform duration-300 z-50">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Keranjang Anda</h2>
        <button onclick="closeSidebar()" class="text-gray-600 hover:text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div id="notifContent" class="mb-4"></div>
    <button onclick="viewCart()" class="w-full bg-yellow-500 text-white px-4 py-2 rounded">Lihat Keranjang</button>
</div>

<!-- Detail Produk -->
<section class="container mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-2 gap-8 mt-11">
    <div>
        <img src="{{ asset('uploads/' . $product->gambar_produk) }}"
             alt="Produk ID {{ $product->produk_id }} - {{ $product->nama_produk }}"
             title="Produk ID {{ $product->produk_id }}"
             data-id="{{ $product->produk_id }}"
             class="rounded-lg shadow-xl w-full">
    </div>

    <div>
        <p class="text-xs {{ $product->stok > 0 ? 'bg-green-600' : 'bg-red-600' }} text-white px-2 py-1 rounded inline-block mb-2">
            {{ $product->stok > 0 ? 'Stok Tersedia' : 'Stok Habis' }}
        </p>
        <h1 class="text-3xl font-bold mb-2">
            {{ $product->nama_produk }} (ID: {{ $product->produk_id }})
        </h1>
        <p class="text-lg text-black font-semibold">Rp {{ number_format($product->harga_diskon ?? $product->harga, 0, ',', '.') }}</p>

        <p class="text-sm text-gray-600 mb-2">Ukuran:</p>
        <div class="flex space-x-4 mb-4">
            <button type="button" class="size-btn px-4 py-2 rounded border-2 border-black bg-white text-black hover:bg-black hover:text-white transition-colors" data-size="S">S</button>
            <button type="button" class="size-btn px-4 py-2 rounded border-2 border-black bg-white text-black hover:bg-black hover:text-white transition-colors" data-size="M">M</button>
            <button type="button" class="size-btn px-4 py-2 rounded border-2 border-black bg-white text-black hover:bg-black hover:text-white transition-colors" data-size="L">L</button>
            <button type="button" class="size-btn px-4 py-2 rounded border-2 border-black bg-white text-black hover:bg-black hover:text-white transition-colors" data-size="XL">XL</button>
        </div>

        <div class="flex items-center space-x-4 mb-6">
            <button id="addToCartButton" type="button" class="flex-1 bg-black text-white py-3 rounded-lg hover:bg-gray-800 transition" {{ $product->stok <= 0 ? 'disabled' : '' }}>
                {{ $product->stok > 0 ? 'Pilih Ukuran Terlebih Dahulu' : 'Stok Habis' }}
            </button>
            <button id="buyNowButton" type="button" class="flex-1 bg-white border border-black py-3 rounded-lg hover:bg-gray-200 transition" {{ $product->stok <= 0 ? 'disabled' : '' }}>
                Beli Sekarang
            </button>
            <button type="button" id="wishlistButton" class="p-2 rounded-full border hover:bg-pink-100 transition">
                <i id="wishlistIcon" class="fa fa-heart wishlist-icon @if($in_wishlist) active text-pink-500 @else text-gray-500 @endif text-xl"></i>
            </button>
        </div>

        <div class="text-sm text-gray-600 space-y-1 mb-6">
            {!! nl2br(e($product->deskripsi)) !!}
        </div>

        <div class="text-sm text-gray-600">
            <p>Pengiriman:</p>
            <p>Dikirim dalam 24 jam. Berat: 250-500g</p>
        </div>
    </div>
</section>

<!-- Bagian Ulasan -->
<section class="container mx-auto px-6 py-8">
    <h2 class="text-2xl font-bold mb-6">Ulasan Produk</h2>
    <div class="bg-white rounded-lg shadow-md p-6">
        @if(session('success_message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
                {{ session('success_message') }}
            </div>
        @endif
        @if(session('error_message'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                {{ session('error_message') }}
            </div>
        @endif

        {{-- Daftar Ulasan - placeholder untuk data ulasan --}}
        <div class="space-y-6">
            <p class="text-gray-600">Belum ada ulasan untuk produk ini.</p>
        </div>
    </div>
</section>

<!-- Modal untuk Ulasan -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 id="modalTitle" class="text-xl font-bold">Beri Ulasan</h2>
            <button onclick="closeReviewModal()" class="text-gray-600 hover:text-gray-900">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <form id="reviewForm" action="{{ url('/submit_review') }}" method="POST">
            @csrf
            <input type="hidden" name="produk_id" id="produkId" value="{{ $product->produk_id }}">
            <input type="hidden" name="transaksi_id" id="transaksiId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Rating</label>
                <div class="flex space-x-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" class="star-button" data-rating="{{ $i }}">
                            <i class="fa fa-star text-gray-300 text-xl"></i>
                        </button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="ratingInput" required>
            </div>
            <div class="mb-4">
                <label for="komentar" class="block text-sm font-medium text-gray-700">Komentar</label>
                <textarea name="komentar" id="komentar" rows="4" class="w-full px-3 py-2 border rounded-lg" placeholder="Tulis ulasan Anda..." required></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Kirim Ulasan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Global variables
window.cart = [];
window.selectedSize = null;

// Size selection function
window.selectSize = function(size, element, evt) {
    console.log('Size selected:', size, 'element:', element);
    
    // Prevent event bubbling
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
    
    window.selectedSize = size;
    
    // Update button states
    document.querySelectorAll('.size-btn').forEach(function(btn) {
        btn.classList.remove('selected', 'bg-black', 'text-white');
        btn.classList.add('bg-white', 'text-black');
    });
    
    // Highlight selected
    if (element) {
        element.classList.remove('bg-white', 'text-black');
        element.classList.add('selected', 'bg-black', 'text-white');
    }
    
    // Enable cart button
    var cartBtn = document.getElementById('addToCartButton');
    if (cartBtn) {
        cartBtn.disabled = false;
        cartBtn.textContent = 'Tambah ke Keranjang';
        console.log('Cart button enabled');
    }
    
    console.log('Size selection complete:', window.selectedSize);
    return false;
};

window.handleAddToCart = function(evt) {
    console.log('Add to cart clicked, selectedSize:', window.selectedSize);
    
    // Prevent event bubbling
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
    
    if (!window.selectedSize) {
        alert('Pilih ukuran terlebih dahulu sebelum menambah ke keranjang.');
        return;
    }

    var productId = {{ $product->produk_id }};
    var size = window.selectedSize;
    var csrfToken = '{{ csrf_token() }}';
    
    console.log('Sending to cart:', {productId: productId, size: size});

    fetch('{{ url("/add_to_cart") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'produk_id=' + encodeURIComponent(productId) + '&size=' + encodeURIComponent(size) + '&_token=' + encodeURIComponent(csrfToken)
    })
    .then(function(response) {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        console.log('Cart response:', data);
        if(data.success) {
            var product = {
                name: '{{ $product->nama_produk }}',
                price: {{ (int)($product->harga_diskon ?? $product->harga) }},
                image: '{{ asset("uploads/" . $product->gambar_produk) }}',
                quantity: 1,
                size: window.selectedSize,
                id: {{ $product->produk_id }}
            };
            window.showNotification(product);
            // Reset selection after successful add
            window.selectedSize = null;
            var cartBtn = document.getElementById('addToCartButton');
            if (cartBtn) {
                cartBtn.disabled = true;
                cartBtn.textContent = 'Pilih Ukuran Terlebih Dahulu';
            }
            document.querySelectorAll('.size-btn').forEach(function(btn) {
                btn.classList.remove('bg-black', 'text-white');
                btn.classList.add('bg-white', 'text-black');
            });
        } else {
            alert(data.message || 'Gagal menambahkan ke keranjang');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Gagal menambahkan ke keranjang. Error: ' + error.message);
    });
};

window.handleBuyNow = function(evt) {
    console.log('Buy now clicked, selectedSize:', window.selectedSize);
    
    // Prevent event bubbling
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
    
    if (!window.selectedSize) {
        alert('Pilih ukuran terlebih dahulu sebelum membeli.');
        return false;
    }
    
    @if(!session()->has('user_id'))
        alert('Anda harus login terlebih dahulu.');
        window.location.href = '{{ url("/users/login") }}';
        return false;
    @endif

    alert('Fitur beli sekarang akan segera hadir!');
    return false;
};

window.toggleWishlist = function(evt) {
    console.log('Wishlist clicked');
    
    // Prevent event bubbling
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
    
    @if(!session()->has('user_id'))
        console.log('User not logged in, redirecting to login');
        alert('Anda harus login terlebih dahulu untuk menambah ke wishlist.');
        window.location.href = '{{ url("/users/login") }}';
        return false;
    @endif

    var productId = {{ $product->produk_id }};
    var isInWishlist = {{ $in_wishlist ? 'true' : 'false' }};
    var action = isInWishlist ? 'remove' : 'add';
    var wishlistIcon = document.getElementById('wishlistIcon');
    var csrfToken = '{{ csrf_token() }}';
    
    console.log('Wishlist action:', action, 'for product:', productId);

    // Toggle UI first
    if (wishlistIcon) {
        wishlistIcon.classList.toggle('active');
        wishlistIcon.classList.toggle('text-pink-500');
        wishlistIcon.classList.toggle('text-gray-500');
    }

    var url = action === 'add' ? '{{ url("/add_to_wishlist") }}' : '{{ url("/remove_from_wishlist") }}';
    console.log('Sending to:', url);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'produk_id=' + encodeURIComponent(productId) + '&_token=' + encodeURIComponent(csrfToken)
    })
    .then(function(response) {
        console.log('Wishlist response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        console.log('Wishlist response:', data);
        if (!data.success) {
            // Revert on error
            if (wishlistIcon) {
                wishlistIcon.classList.toggle('active');
                wishlistIcon.classList.toggle('text-pink-500');
                wishlistIcon.classList.toggle('text-gray-500');
            }
            alert(data.message || 'Gagal update wishlist');
        } else {
            alert(data.message || (action === 'add' ? 'Berhasil ditambahkan ke wishlist!' : 'Berhasil dihapus dari wishlist!'));
        }
    })
    .catch(function(error) {
        console.error('Wishlist error:', error);
        // Revert on error
        if (wishlistIcon) {
            wishlistIcon.classList.toggle('active');
            wishlistIcon.classList.toggle('text-pink-500');
            wishlistIcon.classList.toggle('text-gray-500');
        }
        alert('Gagal update wishlist. Error: ' + error.message);
    });
    
    return false;
};

window.showNotification = function(product) {
    var notifContent = document.getElementById('notifContent');
    if (notifContent) {
        notifContent.innerHTML = '<div class="flex justify-between items-center border-b pb-4 mb-4">' +
            '<img src="' + product.image + '" alt="' + product.name + '" class="w-12 h-12 object-cover">' +
            '<div class="ml-4">' +
            '<h3 class="font-bold">' + product.name + ' (Ukuran: ' + product.size + ')</h3>' +
            '<p>Rp ' + product.price.toLocaleString('id-ID') + '</p>' +
            '<small>ID: ' + product.id + '</small>' +
            '</div>' +
            '</div>';
    }
    
    var notifSidebar = document.getElementById('notifSidebar');
    if (notifSidebar) {
        notifSidebar.classList.add('slide-in');
        notifSidebar.style.transform = 'translateX(0)';

        setTimeout(function() {
            notifSidebar.style.transform = 'translateX(100%)';
        }, 3000);
    }
};

window.closeSidebar = function() {
    var notifSidebar = document.getElementById('notifSidebar');
    if (notifSidebar) {
        notifSidebar.style.transform = 'translateX(100%)';
    }
};

window.viewCart = function() {
    window.location.href = "{{ url('/keranjang') }}";
    window.closeSidebar();
};

window.closeReviewModal = function() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.style.pointerEvents = 'none';
    }
    const form = document.getElementById('reviewForm');
    if (form) form.reset();
    document.getElementById('ratingInput').value = '';
};

window.openReviewModal = function() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        modal.style.pointerEvents = 'auto';
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Product page initializing...');
    
    // SIZE BUTTONS
    const sizeButtons = document.querySelectorAll('.size-btn');
    sizeButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const size = this.getAttribute('data-size');
            console.log('🔘 Size clicked:', size);
            window.selectSize(size, this, e);
        });
    });
    
    // CART BUTTON
    const cartButton = document.getElementById('addToCartButton');
    if (cartButton) {
        cartButton.addEventListener('click', function(e) {
            console.log('🛒 Cart clicked');
            window.handleAddToCart(e);
        });
        if (!window.selectedSize) cartButton.disabled = true;
    }
    
    // WISHLIST BUTTON
    const wishlistButton = document.getElementById('wishlistButton');
    if (wishlistButton) {
        wishlistButton.addEventListener('click', function(e) {
            console.log('❤️ Wishlist clicked');
            window.toggleWishlist(e);
        });
    }
    
    // BUY NOW BUTTON
    const buyNowButton = document.getElementById('buyNowButton');
    if (buyNowButton) {
        buyNowButton.addEventListener('click', function(e) {
            console.log('💳 Buy now clicked');
            window.handleBuyNow(e);
        });
    }
    
    // MODAL
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.style.display = 'none';
        modal.style.pointerEvents = 'none';
    }
    
    console.log('✅ All buttons ready!');
});
</script>
@endsection
