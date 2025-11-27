@extends('layouts.app')

@section('title', 'Keranjang Belanja - LAVIADE')

@section('content')
@if(!session()->has('user_id'))
    <div class="text-center py-12">
        <i class="fa fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
        <p class="text-xl text-gray-600 mb-4">Silakan login terlebih dahulu</p>
        <a href="{{ url('/users/login') }}" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
            Login
        </a>
    </div>
@else
    <main class="container mx-auto px-4 mt-20 py-8">
        <h1 class="text-2xl font-bold mb-6">Keranjang Belanja</h1>
        
        @if(count($cart_items) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="select-all" class="mr-2" onclick="toggleSelectAll()">
                            <label for="select-all" class="text-sm font-medium">Pilih Semua</label>
                        </div>
                        @foreach($cart_items as $item)
                            @php
                                $quantity = isset($item->jumlah) ? (int)$item->jumlah : 1;
                                $price = ($item->harga_diskon ?? $item->harga);
                                $subtotal = $price * $quantity;
                            @endphp
                            <div class="flex items-center border-b pb-4 mb-4">
                                <input type="checkbox" 
                                       class="mr-4 item-checkbox" 
                                       data-keranjang-id="{{ $item->keranjang_id }}" 
                                       data-subtotal="{{ $subtotal }}" 
                                       onchange="updateSummary()">
                                <img src="{{ asset('uploads/' . $item->gambar_produk) }}" 
                                     alt="{{ $item->nama_produk }}" 
                                     class="w-20 h-20 object-cover rounded">
                                
                                <div class="ml-4 flex-1">
                                    <h3 class="font-medium">{{ $item->nama_produk }}</h3>
                                    <p class="text-sm text-gray-600">Size: {{ $item->size }}</p>
                                    <p class="text-sm text-gray-600">Stok: {{ $item->stok }}</p>
                                    <p class="font-semibold">Rp {{ number_format($price, 0, ',', '.') }}</p>
                                    
                                    <div class="flex items-center mt-2">
                                        <button onclick="updateQuantity({{ $item->keranjang_id }}, 'decrease')" 
                                                class="px-2 py-1 bg-gray-200 rounded-l" {{ $quantity <= 1 ? 'disabled' : '' }}>
                                            -
                                        </button>
                                        <span class="px-4 py-1 bg-gray-100">{{ $quantity }}</span>
                                        <button onclick="updateQuantity({{ $item->keranjang_id }}, 'increase')" 
                                                class="px-2 py-1 bg-gray-200 rounded-r" {{ $quantity >= $item->stok ? 'disabled' : '' }}>
                                            +
                                        </button>
                                        <button onclick="removeItem({{ $item->keranjang_id }})" 
                                                class="ml-4 text-red-500 hover:text-red-700">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold mb-4">Ringkasan Belanja</h2>
                        <div class="flex justify-between mb-2">
                            <span>Total Harga (<span id="selected-count">0</span> item)</span>
                            <span>Rp <span id="selected-total">0</span></span>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span>Ongkos Kirim</span>
                            <span>Gratis</span>
                        </div>
                        <div class="border-t pt-4 mb-4">
                            <div class="flex justify-between font-bold">
                                <span>Total</span>
                                <span>Rp <span id="selected-total-final">0</span></span>
                            </div>
                        </div>
                        <button onclick="checkout()" class="w-full bg-black text-white py-3 rounded-lg hover:bg-gray-800 transition" id="checkout-btn" disabled>
                            Checkout
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fa fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
                <p class="text-xl text-gray-600 mb-4">Keranjang belanja Anda kosong</p>
                <a href="{{ url('/') }}" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
                    Lanjutkan Belanja
                </a>
            </div>
        @endif
    </main>
@endif
@endsection

@section('scripts')
<script>
function updateQuantity(cartId, action) {
    fetch('{{ url("/update_cart") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: `cart_id=${cartId}&action=${action}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memperbarui jumlah. Silakan coba lagi.');
    });
}

function removeItem(cartId) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
        fetch('{{ url("/remove_from_cart") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: `cart_id=${cartId}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus item. Silakan coba lagi.');
        });
    }
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateSummary();
}

function updateSummary() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    let total = 0;
    let count = checkboxes.length;
    
    checkboxes.forEach(checkbox => {
        total += parseFloat(checkbox.getAttribute('data-subtotal'));
    });
    
    document.getElementById('selected-count').textContent = count;
    document.getElementById('selected-total').textContent = total.toLocaleString('id-ID');
    document.getElementById('selected-total-final').textContent = total.toLocaleString('id-ID');
    
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = count === 0;
    }
}

function checkout() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Pilih minimal satu item untuk checkout');
        return;
    }
    
    // Redirect ke checkout dengan selected items
    const selectedIds = Array.from(checkboxes).map(cb => cb.getAttribute('data-keranjang-id'));
    window.location.href = '{{ url("/checkout") }}?items=' + selectedIds.join(',');
}

// Initialize summary on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
});
</script>
@endsection
