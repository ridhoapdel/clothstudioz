<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Discount extends Model
{
    protected $table = 'barang_diskon';
    
    protected $fillable = [
        'produk_id',
        'diskon_persen',
        'harga_diskon',
        'mulai_diskon',
        'selesai_diskon'
    ];

    protected $casts = [
        'diskon_persen' => 'integer',
        'harga_diskon' => 'decimal:2',
        'mulai_diskon' => 'date',
        'selesai_diskon' => 'date',
    ];

    public $timestamps = false;

    /**
     * Get the product for this discount
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id', 'produk_id');
    }

    /**
     * Check if discount is currently active
     */
    public function isActive()
    {
        $now = Carbon::now()->toDateString();
        return $now >= $this->mulai_diskon && $now <= $this->selesai_diskon;
    }
}
