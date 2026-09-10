<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Label field dan formatter nilai untuk entitas yang diaudit.
 *
 * Dipisahkan dari App\Models\AuditLog supaya model tidak mengemban
 * basis data terjemahan + formatter tampilan.
 */
final class AuditFieldMap
{
    /** @var array<string, string> */
    private const FIELD_LABELS = [
        // Koleksi Buku
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

        // Anggota / Pengunjung
        'nama' => 'Nama',
        'nip' => 'NIP',
        'instansi_unit' => 'Instansi / Unit Kerja',
        'no_hp' => 'Nomor HP / WhatsApp',
        'aktif' => 'Status Keaktifan',

        // Pengguna
        'name' => 'Nama Petugas',
        'email' => 'Alamat Email',
        'role' => 'Peran Akun',

        // Peminjaman
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

        // Kategori
        'nama_kategori' => 'Nama Kategori',
        'slug' => 'Slug URL Kategori',
    ];

    public static function label(string $key): string
    {
        return self::FIELD_LABELS[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public static function format(string $key, mixed $value): string
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
            return 'Rp '.number_format((float) $value, 0, ',', '.');
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
}
