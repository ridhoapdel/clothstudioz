<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'ulasan';
    
    protected $fillable = [
        'produk_id',
        'user_id',
        'rating',
        'komentar',
        'tanggal_ulasan'
    ];

    protected $casts = [
        'rating' => 'integer',
        'tanggal_ulasan' => 'datetime',
    ];

    /**
     * Get the product for this review
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'produk_id');
    }

    /**
     * Get the user who wrote the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
