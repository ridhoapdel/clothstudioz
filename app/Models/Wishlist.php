<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $table = 'wishlist';
    protected $primaryKey = 'wishlist_id';
    
    protected $fillable = [
        'user_id',
        'produk_id',
        'tanggal_ditambahkan'
    ];

    protected $casts = [
        'tanggal_ditambahkan' => 'date',
    ];

    /**
     * Get the user that owns the wishlist item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the product in wishlist
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'produk_id');
    }
}
