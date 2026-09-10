<?php

namespace App\Livewire\Guest;

use App\Models\Visitor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class VisitorForm extends Component
{
    public string $kategori = 'umum'; // 'umum' atau 'pegawai'

    public string $nama = '';

    public string $nip = '';

    public string $instansi_unit = '';

    public string $no_hp = '';

    public string $keperluan = '';

    public bool $accepted_privacy = false;

    public bool $alreadyCheckedIn = false;

    public function mount(): void
    {
        if (session('visitor_checked_in') && session('visitor_checked_in_at') === now()->toDateString()) {
            $this->alreadyCheckedIn = true;
        }
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function setKategori(string $kategori): void
    {
        if (in_array($kategori, ['umum', 'pegawai'])) {
            $this->kategori = $kategori;
            $this->resetErrorBag();

            // Jika beralih ke umum, kosongkan NIP
            if ($kategori === 'umum') {
                $this->nip = '';
            }

            // Jika keperluan sebelumnya tidak valid di kategori baru, reset
            $validKeperluan = $kategori === 'pegawai'
                ? self::keperluanPegawaiOptions()
                : self::keperluanUmumOptions();

            if ($this->keperluan !== '' && ! array_key_exists($this->keperluan, $validKeperluan)) {
                $this->keperluan = '';
            }
        }
    }

    public static function unitKerjaOptions(): array
    {
        return [
            'Pembinaan' => '1. Pembinaan',
            'Intelijen' => '2. Intelijen',
            'Tindak Pidana Umum' => '3. Pidana Umum',
            'Tindak Pidana Khusus' => '4. Pidana Khusus',
            'Perdata dan Tata Usaha Negara' => '5. Perdata dan Tata Usaha Negara',
            'Pidana Militer' => '6. Pidana Militer',
            'Pengawasan' => '7. Pengawasan',
            'Pemulihan Aset' => '8. Pemulihan Aset',
            'Bagian Tata Usaha' => '9. Bagian Tata Usaha',
        ];
    }

    public static function keperluanPegawaiOptions(): array
    {
        return [
            'Penyusunan Berkas / Dakwaan / Tuntutan' => 'Penyusunan Berkas / Dakwaan / Tuntutan',
            'Riset Yurisprudensi, Doktrin & Peraturan' => 'Riset Yurisprudensi, Doktrin & Peraturan',
            'Mencari Referensi Tugas Kedinasan' => 'Mencari Referensi Tugas Kedinasan',
            'Membaca di Tempat' => 'Membaca di Tempat',
            'Peminjaman Koleksi Buku' => 'Peminjaman Koleksi Buku',
            'Keperluan Kedinasan Lainnya' => 'Keperluan Kedinasan Lainnya',
        ];
    }

    public static function keperluanUmumOptions(): array
    {
        return [
            'Mencari Referensi Hukum & Koleksi' => 'Mencari Referensi Hukum & Koleksi',
            'Membaca di Tempat' => 'Membaca di Tempat',
            'Riset Skripsi / Tesis / Penelitian' => 'Riset Skripsi / Tesis / Penelitian',
            'Studi Pustaka / Kunjungan Lembaga' => 'Studi Pustaka / Kunjungan Lembaga',
            'Keperluan Lainnya' => 'Keperluan Lainnya',
        ];
    }

    protected function rules(): array
    {
        $isPegawai = $this->kategori === 'pegawai';
        $keperluanOptions = $isPegawai
            ? self::keperluanPegawaiOptions()
            : self::keperluanUmumOptions();

        return [
            'kategori' => ['required', 'string', 'in:umum,pegawai'],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => $isPegawai
                ? ['required', 'string', 'min:5', 'max:30']
                : ['nullable', 'string', 'max:30'],
            'instansi_unit' => $isPegawai
                ? ['required', 'string', 'max:255', Rule::in(array_keys(self::unitKerjaOptions()))]
                : ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:100'],
            'keperluan' => ['required', 'string', 'max:255', Rule::in(array_keys($keperluanOptions))],
            'accepted_privacy' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP / NRP wajib diisi untuk anggota/pegawai Kejaksaan.',
            'instansi_unit.required' => $this->kategori === 'pegawai'
                ? 'Unit kerja / Bidang / Satker Kejaksaan wajib diisi.'
                : 'Instansi / Lembaga / Asal pengunjung wajib diisi.',
            'instansi_unit.in' => 'Unit kerja / bidang Kejaksaan tidak valid. Silakan pilih dari daftar yang tersedia.',
            'keperluan.required' => 'Keperluan kunjungan wajib dipilih atau diisi.',
            'keperluan.in' => 'Keperluan kunjungan tidak valid. Silakan pilih dari daftar yang tersedia.',
            'accepted_privacy.accepted' => 'Persetujuan pemrosesan data wajib diberikan.',
        ];
    }

    public function submit(): void
    {
        $throttleKey = 'visitor-form:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            throw ValidationException::withMessages([
                'nama' => 'Terlalu banyak percobaan. Silakan tunggu '.RateLimiter::availableIn($throttleKey).' detik lalu coba lagi.',
            ]);
        }

        if (! $this->accepted_privacy) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('accepted_privacy', 'Persetujuan pemrosesan data wajib diberikan.');

            return;
        }

        try {
            $data = $this->validate();
        } catch (ValidationException $exception) {
            // Hitung juga percobaan gagal agar form buku tamu tidak bisa di-spam
            // entri tidak valid tanpa batas sebelum throttle aktif.
            RateLimiter::hit($throttleKey, 60);

            throw $exception;
        }

        if ($this->kategori === 'umum') {
            $data['nip'] = null;
        }

        $data['kategori'] = $this->kategori;
        unset($data['accepted_privacy']);
        $data['privacy_consented_at'] = now();
        $visitor = Visitor::create($data);
        RateLimiter::hit($throttleKey, 60);
        Cache::forget('admin:dashboard-stats');

        session([
            'visitor_checked_in' => true,
            'visitor_id' => $visitor->id,
            'visitor_checked_in_at' => now()->toDateString(),
            'visitor_kategori' => $visitor->kategori,
        ]);

        $this->redirectRoute('katalog');
    }

    public function render()
    {
        return view('livewire.guest.visitor-form', [
            'unitKerjaList' => self::unitKerjaOptions(),
            'keperluanList' => $this->kategori === 'pegawai'
                ? self::keperluanPegawaiOptions()
                : self::keperluanUmumOptions(),
        ])->layout('layouts.guest', ['title' => 'Buku Tamu Kunjungan']);
    }
}
