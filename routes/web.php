<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;

// Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shopAll', [HomeController::class, 'shopAll'])->name('shopAll');
Route::get('/product/{id}', [HomeController::class, 'product'])->name('product');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/live_search', [HomeController::class, 'liveSearch'])->name('liveSearch');

// Cart and Wishlist Routes
Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang');
Route::get('/wishlist', [CartController::class, 'wishlist'])->name('wishlist');
Route::post('/add_to_cart', [CartController::class, 'addToCart'])->name('add_to_cart');
Route::post('/update_cart', [CartController::class, 'updateCart'])->name('update_cart');
Route::post('/remove_from_cart', [CartController::class, 'removeFromCart'])->name('remove_from_cart');
Route::post('/add_to_wishlist', [CartController::class, 'addToWishlist'])->name('add_to_wishlist');
Route::post('/remove_from_wishlist', [CartController::class, 'removeFromWishlist'])->name('remove_from_wishlist');

// User Authentication Routes
Route::get('/users/login', [UserController::class, 'showLoginForm'])->name('users.login');
Route::post('/users/login', [UserController::class, 'login'])->name('users.login.post');
Route::get('/users/register', [UserController::class, 'showRegisterForm'])->name('users.register');
Route::post('/users/register', [UserController::class, 'register'])->name('users.register.post');
Route::get('/users/logout', [UserController::class, 'logout'])->name('users.logout');

// User Routes
Route::get('/user/profil', [UserController::class, 'profil'])->name('user.profil');
Route::post('/user/update-profil', [UserController::class, 'updateProfil'])->name('user.updateProfil');
Route::post('/user/upload-foto', [UserController::class, 'uploadFoto'])->name('user.uploadFoto');

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login']);
    Route::get('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
    Route::middleware('admin.session')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/produk', [AdminController::class, 'produk'])->name('admin.produk');
        Route::get('/produk/create', [AdminController::class, 'createProduk'])->name('admin.produk.create');
        Route::post('/produk', [AdminController::class, 'storeProduk'])->name('admin.produk.store');
        Route::get('/produk/{id}/edit', [AdminController::class, 'editProduk'])->name('admin.produk.edit');
        Route::put('/produk/{id}', [AdminController::class, 'updateProduk'])->name('admin.produk.update');
        Route::delete('/produk/{id}', [AdminController::class, 'deleteProduk'])->name('admin.produk.delete');
        Route::get('/user', [AdminController::class, 'user'])->name('admin.user');
        Route::get('/transaksi', [AdminController::class, 'transaksi'])->name('admin.transaksi');
        Route::get('/profil', [AdminController::class, 'profil'])->name('admin.profil');
        Route::post('/profil', [AdminController::class, 'updateProfil'])->name('admin.updateProfil');
    });
});
