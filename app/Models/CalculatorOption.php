<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CalculatorOption extends Model
{
    public const GROUPS = [
        'product' => 'Вид продукту (собівартість за 1 мл, ₴)',
        'formula' => 'Складність формули (множник)',
        'packaging' => 'Упаковка (₴ за штуку)',
        'label' => 'Етикетка (₴ за штуку)',
        'box' => 'Коробка (₴ за штуку)',
    ];

    protected $fillable = [
        'group',
        'name',
        'value',
        'image',
        'hint',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'value' => 'float',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
