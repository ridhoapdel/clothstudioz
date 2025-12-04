<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $table = 'keranjang';
    protected $primaryKey = 'keranjang_id';
    
    protected $fillable = [
        'user_id',
        'produk_id',
        'size',
        'jumlah'
    ];

    protected $casts = [
        'jumlah' => 'integer',
    ];

    /**
     * Get the user that owns the cart item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the product in cart
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'produk_id');
    }

    /**
     * Get subtotal for this cart item
     */
    public function getSubtotalAttribute()
    {
        return $this->product->effective_price * $this->jumlah;
    }
}
