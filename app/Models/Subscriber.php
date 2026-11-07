<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $fillable = ['email', 'name', 'is_active', 'welcome_promo_code'];

    protected $casts = ['is_active' => 'boolean'];
}
