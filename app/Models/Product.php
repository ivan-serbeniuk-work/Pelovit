<?php

namespace App\Models;

use App\Models\Concerns\HasYoutubeId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    use HasYoutubeId;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $product) {
            if (empty($product->slug)) {
                $product->slug = static::uniqueSlug(Str::slug($product->name));
            }
        });
    }

    private static function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'instruction',
        'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'no_index',
        'price', 'old_price', 'image', 'images', 'video_url', 'videos',
        'brand', 'stock', 'is_active', 'is_featured',
    ];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'no_index' => 'boolean',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
    ];

    protected $appends = ['discount_percent', 'youtube_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->old_price || $this->old_price <= $this->price) {
            return null;
        }

        return (int) round((1 - $this->price / $this->old_price) * 100);
    }
}
