<?php

namespace App\Livewire\Guest;

use App\Models\Visitor;
use Illuminate\Support\Facades\RateLimiter;
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

    public function setKategori(string $kategori): void
    {
        if (in_array($kategori, ['umum', 'pegawai'])) {
            $this->kategori = $kategori;
            $this->resetErrorBag();

            // Jika beralih ke umum, kosongkan NIP
            if ($kategori === 'umum') {
                $this->nip = '';
            }
        }
    }

    protected function rules(): array
    {
        $isPegawai = $this->kategori === 'pegawai';

        return [
            'kategori' => ['required', 'string', 'in:umum,pegawai'],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => $isPegawai
                ? ['required', 'string', 'min:5', 'max:30']
                : ['nullable', 'string', 'max:30'],
            'instansi_unit' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:100'],
            'keperluan' => ['required', 'string', 'max:255'],
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
            'keperluan.required' => 'Keperluan kunjungan wajib dipilih atau diisi.',
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

        $data = $this->validate();

        if ($this->kategori === 'umum') {
            $data['nip'] = null;
        }

        $data['kategori'] = $this->kategori;
        unset($data['accepted_privacy']);
        $data['privacy_consented_at'] = now();
        $visitor = Visitor::create($data);
        RateLimiter::hit($throttleKey, 60);

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
        $statistics = Visitor::query()
            ->whereBetween('created_at', [today()->startOfDay(), today()->endOfDay()])
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN kategori = 'pegawai' THEN 1 ELSE 0 END) as pegawai")
            ->selectRaw("SUM(CASE WHEN kategori = 'umum' THEN 1 ELSE 0 END) as umum")
            ->first();

        return view('livewire.guest.visitor-form', [
            'todayVisitors' => (int) $statistics->total,
            'todayPegawai' => (int) $statistics->pegawai,
            'todayUmum' => (int) $statistics->umum,
        ])->layout('layouts.guest', ['title' => 'Buku Tamu Kunjungan']);
    }
}
