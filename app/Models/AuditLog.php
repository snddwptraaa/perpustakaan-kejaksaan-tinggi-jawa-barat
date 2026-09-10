<?php

namespace App\Models;

use App\Support\AuditFieldMap;
use App\Support\AuditSummarizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'aksi',
        'entitas',
        'entitas_id',
        'sebelum',
        'sesudah',
        'metadata',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'sebelum' => 'array',
            'sesudah' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEntityLabelAttribute(): string
    {
        return match ($this->entitas) {
            'books' => 'Koleksi Buku',
            'loans' => 'Peminjaman',
            'members' => 'Anggota',
            'users' => 'Pengguna Admin',
            'categories' => 'Kategori Buku',
            default => ucfirst($this->entitas),
        };
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->aksi) {
            'buat' => 'Tambah Data',
            'ubah' => 'Ubah Data',
            'koreksi' => 'Koreksi Data',
            'hapus' => 'Hapus Data',
            'pinjam' => 'Peminjaman',
            'kembali' => 'Pengembalian',
            'perpanjang' => 'Perpanjangan',
            'batal' => 'Pembatalan',
            'arsipkan' => 'Arsipkan',
            'pulihkan' => 'Pulihkan',
            'nonaktifkan' => 'Nonaktifkan',
            default => ucfirst($this->aksi),
        };
    }

    public function getActionBadgeColorAttribute(): string
    {
        return match ($this->aksi) {
            'buat' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'ubah', 'koreksi' => 'bg-blue-50 text-blue-700 border-blue-200',
            'hapus' => 'bg-rose-50 text-rose-700 border-rose-200',
            'pinjam' => 'bg-amber-50 text-amber-800 border-amber-200',
            'kembali' => 'bg-teal-50 text-teal-700 border-teal-200',
            'perpanjang' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'batal' => 'bg-orange-50 text-orange-700 border-orange-200',
            'arsipkan' => 'bg-stone-100 text-stone-700 border-stone-300',
            'pulihkan' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            'nonaktifkan' => 'bg-slate-100 text-slate-700 border-slate-300',
            default => 'bg-stone-50 text-stone-700 border-stone-200',
        };
    }

    public function getSubjectTitleAttribute(): ?string
    {
        if (! empty($this->metadata['judul_buku'])) {
            return (string) $this->metadata['judul_buku'];
        }

        if (! empty($this->metadata['nama_peminjam'])) {
            return (string) $this->metadata['nama_peminjam'];
        }

        $sources = array_filter([$this->sesudah, $this->sebelum]);
        foreach ($sources as $source) {
            if (! empty($source['judul'])) {
                return (string) $source['judul'];
            }
            if (! empty($source['nama_peminjam'])) {
                return (string) $source['nama_peminjam'];
            }
            if (! empty($source['nama'])) {
                return (string) $source['nama'];
            }
            if (! empty($source['name'])) {
                return (string) $source['name'];
            }
            if (! empty($source['nama_kategori'])) {
                return (string) $source['nama_kategori'];
            }
        }

        return null;
    }

    public static function getFieldLabel(string $key): string
    {
        return AuditFieldMap::label($key);
    }

    public static function formatFieldValue(string $key, mixed $value): string
    {
        return AuditFieldMap::format($key, $value);
    }

    public function getDifferences(): array
    {
        return AuditSummarizer::differences($this);
    }

    public function getSummaryAttribute(): string
    {
        return AuditSummarizer::summary($this);
    }
}
