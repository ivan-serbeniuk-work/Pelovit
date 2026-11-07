<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public const PAGES = [
        'home' => 'Головна',
        'contract' => 'Контрактне виробництво',
        'opt' => 'Опт',
        'masters' => 'Майстрам',
    ];

    protected $fillable = [
        'page',
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query, string $page): Builder
    {
        return $query->where('page', $page)->where('is_active', true)->orderBy('sort_order');
    }
}
