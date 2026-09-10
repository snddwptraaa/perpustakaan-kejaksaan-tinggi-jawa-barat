<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'jumlah_halaman', 'isbn',
        'no_klasifikasi', 'lokasi_rak', 'stok', 'stok_tersedia', 'cover_image', 'archived_at', 'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'tahun_terbit' => 'integer',
            'stok' => 'integer',
            'stok_tersedia' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Book $book): void {
            if ($book->stok_tersedia > $book->stok) {
                throw new DomainException('Stok tersedia tidak boleh melebihi total stok.');
            }

            if ($book->stok_tersedia < 0) {
                throw new DomainException('Stok tersedia tidak boleh kurang dari nol.');
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->stok_tersedia > 0;
    }
}
