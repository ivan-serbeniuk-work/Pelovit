<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

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
        'price', 'old_price', 'image', 'images', 'video_url',
        'brand', 'stock', 'is_active', 'is_featured',
    ];

    protected $casts = [
        'images' => 'array',
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

    // ID ролика з будь-якого формату посилання: watch?v=, youtu.be/, shorts/, embed/.
    public function getYoutubeIdAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([\w-]{11})~', $this->video_url, $m);

        return $m[1] ?? null;
    }
}
