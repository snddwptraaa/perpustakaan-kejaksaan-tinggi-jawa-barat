<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'jumlah_halaman', 'isbn',
        'no_klasifikasi', 'lokasi_rak', 'stok', 'stok_tersedia', 'cover_image', 'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'tahun_terbit' => 'integer',
            'stok' => 'integer',
            'stok_tersedia' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Book $book): void {
            if ($book->stok_tersedia > $book->stok) {
                throw new InvalidArgumentException('Stok tersedia tidak boleh melebihi total stok.');
            }

            if ($book->stok_tersedia < 0) {
                throw new InvalidArgumentException('Stok tersedia tidak boleh kurang dari nol.');
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->stok_tersedia > 0;
    }
}
