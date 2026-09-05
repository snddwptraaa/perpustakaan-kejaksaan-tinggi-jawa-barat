<?php

namespace App\Models;

use Carbon\Carbon;
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
        $labels = [
            'judul' => 'Judul Buku',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 'Tahun Terbit',
            'jumlah_halaman' => 'Jumlah Halaman',
            'isbn' => 'ISBN',
            'no_klasifikasi' => 'No. Klasifikasi / DDC',
            'lokasi_rak' => 'Lokasi Rak',
            'stok' => 'Total Stok',
            'stok_tersedia' => 'Stok Tersedia',
            'category_id' => 'Kategori (ID)',
            'deskripsi' => 'Deskripsi / Sinopsis',
            'cover_image' => 'File Sampul Buku',
            'archived_at' => 'Waktu Pengarsipan',

            'nama' => 'Nama',
            'nip' => 'NIP',
            'instansi_unit' => 'Instansi / Unit Kerja',
            'no_hp' => 'Nomor HP / WhatsApp',
            'aktif' => 'Status Keaktifan',

            'name' => 'Nama Petugas',
            'email' => 'Alamat Email',
            'role' => 'Peran Akun',

            'nama_peminjam' => 'Nama Peminjam',
            'nip_peminjam' => 'NIP Peminjam',
            'book_id' => 'Buku (ID)',
            'member_id' => 'Anggota (ID)',
            'petugas_id' => 'Petugas Pencatat (ID)',
            'petugas_pembatal_id' => 'Petugas Pembatal (ID)',
            'tanggal_pinjam' => 'Tanggal Pinjam',
            'tanggal_jatuh_tempo' => 'Batas Jatuh Tempo',
            'tanggal_kembali' => 'Tanggal Pengembalian',
            'tanggal_dibatalkan' => 'Tanggal Dibatalkan',
            'catatan' => 'Catatan Transaksi',
            'jumlah_perpanjangan' => 'Jumlah Perpanjangan',
            'tanggal_perpanjangan' => 'Tanggal Perpanjangan',
            'denda' => 'Nominal Denda',
            'denda_dibayar' => 'Denda Dibayar',
            'denda_dibayar_pada' => 'Tanggal Bayar Denda',

            'nama_kategori' => 'Nama Kategori',
            'slug' => 'Slug URL Kategori',
        ];

        return $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public static function formatFieldValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            if ($key === 'aktif') {
                return $value ? 'Aktif' : 'Nonaktif';
            }

            return $value ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (in_array($key, ['denda', 'denda_dibayar'], true) && is_numeric($value)) {
            return 'Rp ' . number_format((float) $value, 0, ',', '.');
        }

        if (str_ends_with($key, '_at') || str_starts_with($key, 'tanggal_')) {
            try {
                $carbon = Carbon::parse($value);

                return str_ends_with($key, '_at')
                    ? $carbon->translatedFormat('d M Y H:i')
                    : $carbon->translatedFormat('d M Y');
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        if ($key === 'role') {
            return match ((string) $value) {
                'superadmin' => 'Superadmin',
                'admin' => 'Petugas Admin',
                default => (string) $value,
            };
        }

        return (string) $value;
    }

    public function getDifferences(): array
    {
        if (! is_array($this->sebelum) || ! is_array($this->sesudah)) {
            return [];
        }

        $ignoredKeys = [
            'id', 'created_at', 'updated_at', 'password',
            'remember_token', 'email_verified_at',
        ];

        $diffs = [];
        $allKeys = array_unique(array_merge(array_keys($this->sebelum), array_keys($this->sesudah)));

        foreach ($allKeys as $key) {
            if (in_array($key, $ignoredKeys, true)) {
                continue;
            }

            $valBefore = $this->sebelum[$key] ?? null;
            $valAfter = $this->sesudah[$key] ?? null;

            $beforeNorm = is_numeric($valBefore) ? (string) $valBefore : $valBefore;
            $afterNorm = is_numeric($valAfter) ? (string) $valAfter : $valAfter;

            if ($beforeNorm !== $afterNorm) {
                $diffs[] = [
                    'key' => $key,
                    'label' => self::getFieldLabel($key),
                    'old_raw' => $valBefore,
                    'new_raw' => $valAfter,
                    'old_formatted' => self::formatFieldValue($key, $valBefore),
                    'new_formatted' => self::formatFieldValue($key, $valAfter),
                ];
            }
        }

        return $diffs;
    }

    public function getSummaryAttribute(): string
    {
        $subject = $this->subject_title;

        if (in_array($this->aksi, ['ubah', 'koreksi'], true)) {
            $diffs = $this->getDifferences();
            if (count($diffs) === 1) {
                $diff = $diffs[0];

                return "Mengubah {$diff['label']}: {$diff['old_formatted']} → {$diff['new_formatted']}";
            } elseif (count($diffs) > 1) {
                $labels = array_column(array_slice($diffs, 0, 3), 'label');
                $extra = count($diffs) > 3 ? ' +' . (count($diffs) - 3) . ' lainnya' : '';

                return 'Mengubah ' . count($diffs) . ' kolom (' . implode(', ', $labels) . $extra . ')';
            }

            return $subject ? "Pembaruan data pada {$subject}" : 'Pembaruan data';
        }

        if ($this->aksi === 'buat') {
            return $subject ? "Menambahkan data baru: {$subject}" : 'Menambahkan data baru';
        }

        if ($this->aksi === 'hapus') {
            return $subject ? "Menghapus data: {$subject}" : 'Menghapus data';
        }

        if ($this->aksi === 'pinjam') {
            $borrower = $this->metadata['nama_peminjam'] ?? $this->sesudah['nama_peminjam'] ?? null;
            $book = $this->metadata['judul_buku'] ?? null;
            if ($borrower && $book) {
                return "Peminjaman: {$borrower} meminjam \"{$book}\"";
            }

            return $borrower ? "Peminjaman oleh {$borrower}" : 'Pencatatan peminjaman buku';
        }

        if ($this->aksi === 'kembali') {
            $book = $this->metadata['judul_buku'] ?? null;
            $borrower = $this->metadata['nama_peminjam'] ?? null;
            if ($book) {
                return "Pengembalian buku: \"{$book}\"" . ($borrower ? " ({$borrower})" : '');
            }

            return 'Konfirmasi pengembalian buku';
        }

        if ($this->aksi === 'perpanjang') {
            $newDue = $this->sesudah['tanggal_jatuh_tempo'] ?? null;
            $dateFormatted = $newDue ? Carbon::parse($newDue)->translatedFormat('d M Y') : '';

            return 'Perpanjangan jatuh tempo' . ($dateFormatted ? " s.d. {$dateFormatted}" : '');
        }

        if ($this->aksi === 'batal') {
            return 'Pembatalan transaksi' . ($subject ? " ({$subject})" : '') . ' — stok dipulihkan';
        }

        if ($this->aksi === 'arsipkan') {
            return 'Mengarsipkan buku' . ($subject ? ": {$subject}" : '') . ' dari katalog aktif';
        }

        if ($this->aksi === 'pulihkan') {
            return 'Memulihkan buku' . ($subject ? ": {$subject}" : '') . ' ke katalog aktif';
        }

        if ($this->aksi === 'nonaktifkan') {
            return 'Menonaktifkan data' . ($subject ? ": {$subject}" : '');
        }

        return ucfirst($this->aksi) . ($subject ? " pada {$subject}" : '');
    }
}
