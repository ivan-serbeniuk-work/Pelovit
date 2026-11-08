<?php

namespace App\Models;

use App\Models\Concerns\HasYoutubeId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    use HasYoutubeId;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'image',
        'video_url', 'video_file', 'video_poster',
        'category', 'tags', 'is_published', 'is_featured', 'published_at',
        'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'no_index',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'no_index' => 'boolean',
        'published_at' => 'datetime',
        'tags' => 'array',
    ];

    protected $appends = ['formatted_date', 'youtube_id'];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getFormattedDateAttribute(): string
    {
        $date = $this->published_at ?? $this->created_at;

        return $date?->translatedFormat('j F Y') ?? '';
    }
}
