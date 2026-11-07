<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutFounder extends Model
{
    protected $table = 'about_founder';

    protected $fillable = ['name', 'role', 'quote', 'photo', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Картка на сторінці одна — беремо перший активний запис. */
    public static function current(): ?self
    {
        return static::where('is_active', true)->orderBy('id')->first();
    }
}
