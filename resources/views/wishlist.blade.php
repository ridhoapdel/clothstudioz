@extends('layouts.app')

@section('title', 'Wishlist - LAVIADE')

@section('content')
@if(!session()->has('user_id'))
    <div class="text-center py-12">
        <i class="fa fa-heart text-5xl text-gray-300 mb-4"></i>
        <p class="text-xl text-gray-600 mb-4">Silakan login terlebih dahulu</p>
        <a href="{{ url('/users/login') }}" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
            Login
        </a>
    </div>
@else
    <main class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6 mt-20">Wishlist Saya</h1>
        
        @if(count($wishlist_items) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($wishlist_items as $item)
                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                        <a href="{{ url('/product/' . $item->produk_id) }}">
                            <img src="{{ asset('uploads/' . $item->gambar_produk) }}" 
                                 alt="{{ $item->nama_produk }}" 
                                 class="w-full h-64 object-cover">
                        </a>
                        <div class="p-4">
                            <h3 class="font-medium text-lg mb-1">
                                <a href="{{ url('/product/' . $item->produk_id) }}">
                                    {{ $item->nama_produk }}
                                </a>
                            </h3>
                            <p class="text-gray-600 mb-2">Rp {{ number_format($item->harga_diskon ?? $item->harga, 0, ',', '.') }}</p>
                            <div class="flex justify-between items-center">
                                <button onclick="window.location.href='{{ url('/product/' . $item->produk_id) }}'" 
                                        class="text-sm bg-black text-white px-3 py-1 rounded hover:bg-gray-800">
                                    Lihat Produk
                                </button>
                                <button onclick="removeFromWishlist({{ $item->wishlist_id }})" 
                                        class="text-red-500 hover:text-red-700">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <i class="fa fa-heart text-5xl text-gray-300 mb-4"></i>
                <p class="text-xl text-gray-600 mb-4">Wishlist Anda kosong</p>
                <a href="{{ url('/shopAll') }}" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
                    Mulai Belanja
                </a>
            </div>
        @endif
    </main>
@endif
@endsection

@section('scripts')
<script>
function removeFromWishlist(wishlistId) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini dari wishlist?')) {
        fetch('{{ url("/remove_from_wishlist") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: `wishlist_id=${wishlistId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus item dari wishlist.');
        });
    }
}
</script>
@endsection
