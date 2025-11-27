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
    }
    .modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: 0.5rem;
        width: 90%;
        max-width: 500px;
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
            <button class="size-button px-4 py-2 rounded border hover:bg-gray-800 hover:text-white" data-size="S">S</button>
            <button class="size-button px-4 py-2 rounded border hover:bg-gray-800 hover:text-white" data-size="M">M</button>
            <button class="size-button px-4 py-2 rounded border hover:bg-gray-800 hover:text-white" data-size="L">L</button>
            <button class="size-button px-4 py-2 rounded border hover:bg-gray-800 hover:text-white" data-size="XL">XL</button>
        </div>

        <div class="flex items-center space-x-4 mb-6">
            <button id="addToCartButton"
                    data-id="{{ $product->produk_id }}"
                    onclick="addToCart()" 
                    class="flex-1 bg-black text-white py-3 rounded-lg hover:bg-gray-800 transition"
                    {{ $product->stok <= 0 ? 'disabled' : '' }}>
                {{ $product->stok > 0 ? 'Pilih Ukuran Terlebih Dahulu' : 'Stok Habis' }}
            </button>
            <button onclick="buyNow('{{ $product->nama_produk }}', {{ (int)($product->harga_diskon ?? $product->harga) }})" class="flex-1 bg-white border border-black py-3 rounded-lg hover:bg-gray-200 transition" {{ $product->stok <= 0 ? 'disabled' : '' }}>
                Beli Sekarang
            </button>
            <button id="wishlistButton" onclick="toggleWishlist()" class="p-2 rounded-full border hover:bg-pink-100 transition">
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
    let cart = [];
    let selectedSize = null;

    // Initialize exactly like clothStudio
    document.querySelectorAll('.size-button').forEach(button => {
        button.addEventListener('click', function() {
            selectedSize = this.getAttribute('data-size');
            document.getElementById('addToCartButton').disabled = false;
            document.querySelectorAll('.size-button').forEach(btn => btn.classList.remove('bg-gray-800', 'text-white'));
            this.classList.add('bg-gray-800', 'text-white');
        });
    });

    function toggleWishlist() {
        // Check login exactly like clothStudio
        @if(!session()->has('user_id'))
            window.location.href = '{{ url("/users/login") }}';
            return;
        @endif

        const productId = {{ $product->produk_id }};
        const isInWishlist = {{ $in_wishlist ? 'true' : 'false' }};
        const action = isInWishlist ? 'remove' : 'add';
        const wishlistIcon = document.getElementById('wishlistIcon');

        // Toggle UI first (like clothStudio)
        wishlistIcon.classList.toggle('active');
        wishlistIcon.classList.toggle('text-pink-500');
        wishlistIcon.classList.toggle('text-gray-500');

        // Send to appropriate endpoint
        fetch(action === 'add' ? '{{ url("/add_to_wishlist") }}' : '{{ url("/remove_from_wishlist") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: `produk_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert exactly like clothStudio
                wishlistIcon.classList.toggle('active');
                wishlistIcon.classList.toggle('text-pink-500');
                wishlistIcon.classList.toggle('text-gray-500');
                alert(data.message || 'Gagal update wishlist');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert exactly like clothStudio
            wishlistIcon.classList.toggle('active');
            wishlistIcon.classList.toggle('text-pink-500');
            wishlistIcon.classList.toggle('text-gray-500');
            alert('Gagal update wishlist. Silakan coba lagi.');
        });
    }

    function addToCart() {
        if (!selectedSize) {
            alert('Pilih ukuran terlebih dahulu sebelum menambah ke keranjang.');
            return;
        }

        const productId = {{ $product->produk_id }};
        const size = selectedSize;

        fetch('{{ url("/add_to_cart") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: `produk_id=${productId}&size=${size}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                const product = {
                    name: '{{ $product->nama_produk }}',
                    price: {{ (int)($product->harga_diskon ?? $product->harga) }},
                    image: '{{ asset("uploads/" . $product->gambar_produk) }}',
                    quantity: 1,
                    size: selectedSize,
                    id: {{ $product->produk_id }}
                };
                showNotification(product);
                // Reset selection after successful add
                selectedSize = null;
                document.getElementById('addToCartButton').disabled = true;
                document.querySelectorAll('.size-button').forEach(btn => btn.classList.remove('bg-gray-800', 'text-white'));
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menambahkan ke keranjang. Silakan coba lagi atau login terlebih dahulu.');
        });
    }

    function showNotification(product) {
        const notifContent = document.getElementById('notifContent');
        notifContent.innerHTML = `
            <div class="flex justify-between items-center border-b pb-4 mb-4">
                <img src="${product.image}" alt="${product.name}" class="w-12 h-12 object-cover">
                <div class="ml-4">
                    <h3 class="font-bold">${product.name} (Ukuran: ${product.size})</h3>
                    <p>Rp ${product.price.toLocaleString('id-ID')}</p>
                    <small>ID: ${product.id}</small>
                </div>
            </div>
        `;
        const notifSidebar = document.getElementById('notifSidebar');
        notifSidebar.classList.add('slide-in');
        notifSidebar.style.transform = 'translateX(0)';

        setTimeout(() => {
            notifSidebar.style.transform = 'translateX(100%)';
        }, 3000);
    }

    function closeSidebar() {
        document.getElementById('notifSidebar').style.transform = 'translateX(100%)';
    }

    function viewCart() {
        window.location.href = "{{ url('/keranjang') }}";
        closeSidebar();
    }

    function buyNow(productName, productPrice) {
        if (!selectedSize) {
            alert('Pilih ukuran terlebih dahulu sebelum membeli.');
            return;
        }
        
        @if(!session()->has('user_id'))
            alert('Anda harus login terlebih dahulu.');
            window.location.href = '{{ url("/users/login") }}';
            return;
        @endif

        alert('Fitur beli sekarang akan segera hadir!');
    }
</script>
@endsection
