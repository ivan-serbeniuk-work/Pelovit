<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name', 'slug', 'parent_id', 'image', 'is_active', 'sort_order'];

    /** @var list<string> */
    protected static array $searchColumns = ['name'];

    /** У категорій немає is_featured — нічия розбивається порядком і id. */
    protected static function searchTieBreakers(): array
    {
        return [];
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
