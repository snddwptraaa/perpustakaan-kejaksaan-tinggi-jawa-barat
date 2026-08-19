<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id', 'petugas_id', 'nama_peminjam', 'nip_peminjam', 'instansi_unit',
        'tanggal_pinjam', 'tanggal_jatuh_tempo', 'tanggal_kembali', 'status', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'tanggal_kembali' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Loan $loan): void {
            if ($loan->tanggal_jatuh_tempo?->lt($loan->tanggal_pinjam)) {
                throw new \InvalidArgumentException('Tanggal jatuh tempo tidak boleh lebih awal dari tanggal pinjam.');
            }
        });
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('tanggal_kembali');
    }

    public function getCurrentStatusAttribute(): string
    {
        if ($this->tanggal_kembali) {
            return 'dikembalikan';
        }

        if ($this->tanggal_jatuh_tempo?->isBefore(Carbon::today())) {
            return 'terlambat';
        }

        return 'dipinjam';
    }
}
