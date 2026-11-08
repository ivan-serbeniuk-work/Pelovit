<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'subtitle', 'image', 'button_text', 'link', 'product_id', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Товар, який показується карткою в цьому слайді (необов'язковий). */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
