<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['nama_kategori', 'slug'];

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            $category->slug = $category->slug ?: Str::slug($category->nama_kategori);
        });
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
