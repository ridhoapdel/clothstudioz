@if(request()->get('query') && !request()->get('from_search_results'))
    @php
        $query = urlencode(trim(request()->get('query')));
        return redirect()->to('/search?query=' . $query . '&from_search_results=1');
    @endphp
@endif

<header>
    <main>
        <!-- Navbar -->
        <nav id="navbar" class="fixed top-0 left-0 right-0 z-40 flex justify-between items-center p-2 bg-white shadow-sm">
            <div class="relative">
                <!-- Hamburger Button -->
                <button id="hamburger" class="p-2 text-black">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
            
            <!-- Logo Tetap di Tengah -->
            <a href="{{ url('/') }}" class="absolute left-1/2 transform -translate-x-1/2">
                <img id="logo-image" src="{{ asset('aset/png logo.png') }}" class="object-cover h-14 w-14">
            </a>
            
            <!-- Navbar Buttons -->
            <div class="flex space-x-4 items-center">
                <!-- Search Icon Button -->
                <button id="search-toggle" class="flex items-center transition duration-300 hover:scale-105">
                    <svg class="text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                    </svg>
                </button>
                
                <!-- Cart Icon -->
                <a href="{{ url('/keranjang') }}" class="flex items-center transition duration-300 hover:scale-105">
                    <svg class="text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-3h9.25L19 7H7.312"/>
                    </svg>
                </a>
                
                <!-- User Icon -->
                @if(session()->has('user_id'))
                    <a href="{{ url('/user/profil') }}" class="flex items-center transition duration-300 hover:scale-105">
                        <svg class="text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="37" height="37" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-width="1.5" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0-3-3h-4a3 3 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ url('/users/login') }}" class="flex items-center transition duration-300 hover:scale-105">
                        <svg class="text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="37" height="37" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-width="1.5" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0-3-3h-4a3 3 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                    </a>
                @endif
            </div>
            
            <!-- Search Bar (Awalnya Tersembunyi) -->
            <div id="search-container">
                <form action="{{ url('/search') }}" method="GET" class="flex items-center w-full max-w-4xl mx-auto relative">
                    <button type="button" id="close-search" class="mr-4 text-gray-500 hover:text-black">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <input type="text" name="query" id="search-input" placeholder="Search..." 
                           class="flex-1 py-2 px-4 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent" 
                           autocomplete="off">
                    <button type="submit" class="ml-4 p-2 text-gray-500 hover:text-black">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>
                </form>
            </div>
        </nav>
        
        <!-- Mobile Menu -->
        <div id="menu" class="fixed top-0 left-0 w-72 h-full bg-white backdrop-blur-md bg-opacity-90 transform -translate-x-full transition-transform duration-300 z-50">
            <button id="close" class="absolute top-4 right-4 text-black">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <nav class="flex flex-col space-y-4 p-6">
                <a href="{{ url('/') }}" class="text-base font-medium text-gray-500 hover:text-black">HOME</a>
                <a href="{{ url('/shopAll') }}" class="text-base font-medium text-gray-500 hover:text-black">SHOPS</a>
                <a href="#" class="text-base font-medium text-gray-500 hover:text-black">CATEGORIES</a>
                <a href="#" class="text-base font-medium text-gray-500 hover:text-black">INSPIRASI GAYA</a>
                <a href="#" class="text-base font-medium text-gray-500 hover:text-black">ABOUT US</a>
                <a href="#" class="text-base font-medium text-gray-500 hover:text-black">CONTACT US</a>
                
                @if(session()->has('user_id'))
                    <a href="{{ url('/user/profil') }}" class="text-base font-medium text-gray-500 hover:text-black">PROFIL</a>
                    <a href="{{ url('/keranjang') }}" class="text-base font-medium text-gray-500 hover:text-black">KERANJANG</a>
                    <a href="{{ url('/wishlist') }}" class="text-base font-medium text-gray-500 hover:text-black">WISHLIST</a>
                    <a href="{{ url('/users/logout') }}" class="text-base font-medium text-red-600 hover:text-red-800">LOGOUT</a>
                @else
                    <a href="{{ url('/users/login') }}" class="text-base font-medium text-gray-500 hover:text-black">LOGIN</a>
                    <a href="{{ url('/users/register') }}" class="text-base font-medium text-gray-500 hover:text-black">REGISTER</a>
                @endif
            </nav>
        </div>
    </main>
</header>

<style>
    #search-container {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 50;
    }
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        max-height: 400px;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 40;
    }
    .search-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
    }
    .search-item:hover {
        background-color: #f8f9fa;
    }
    .search-item-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        margin-right: 10px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // JavaScript untuk menu mobile
    const hamburger = document.getElementById('hamburger');
    const menu = document.getElementById('menu');
    const closeMenu = document.getElementById('close');
    
    if (hamburger && menu && closeMenu) {
        hamburger.addEventListener('click', function() {
            menu.classList.remove('-translate-x-full');
        });

        closeMenu.addEventListener('click', function() {
            menu.classList.add('-translate-x-full');
        });
    }

    // JavaScript untuk search toggle
    const searchToggle = document.getElementById('search-toggle');
    const searchContainer = document.getElementById('search-container');
    const closeSearch = document.getElementById('close-search');

    if (searchToggle && searchContainer && closeSearch) {
        // Sembunyikan search container saat pertama kali load
        searchContainer.style.display = 'none';
        
        searchToggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle display search container
            if (searchContainer.style.display === 'none') {
                searchContainer.style.display = 'block';
                // Scroll ke atas
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                searchContainer.style.display = 'none';
            }
        });

        closeSearch.addEventListener('click', function(e) {
            e.preventDefault();
            searchContainer.style.display = 'none';
        });
    }
});
</script>
