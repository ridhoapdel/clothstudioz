<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'produk_id';
    
    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'harga',
        'stok',
        'stok_s',
        'stok_m',
        'stok_l',
        'stok_xl',
        'gambar_produk',
        'kategori',
        'warna',
        'brand'
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
        'stok_s' => 'integer',
        'stok_m' => 'integer',
        'stok_l' => 'integer',
        'stok_xl' => 'integer',
    ];

    /**
     * Get the discount for the product
     */
    public function discount()
    {
        return $this->hasOne(Discount::class, 'produk_id', 'produk_id');
    }

    /**
     * Get the reviews for the product
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'produk_id', 'produk_id');
    }

    /**
     * Get users who wishlisted this product
     */
    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlist', 'produk_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get cart items for this product
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class, 'produk_id', 'produk_id');
    }

    /**
     * Get the effective price (after discount if applicable)
     */
    public function getEffectivePriceAttribute()
    {
        $discount = $this->discount;
        if ($discount && $discount->isActive()) {
            return $discount->harga_diskon;
        }
        return $this->harga;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock($size = null, $quantity = 1)
    {
        if ($size) {
            // Validate size input
            $allowedSizes = ['s', 'm', 'l', 'xl'];
            $sizeLower = strtolower($size);
            
            if (!in_array($sizeLower, $allowedSizes)) {
                return false;
            }
            
            $stockField = 'stok_' . $sizeLower;
            return $this->$stockField >= $quantity;
        }
        return $this->stok >= $quantity;
    }

    /**
     * Get average rating
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
}
