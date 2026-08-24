<?php

namespace App\Livewire\Guest;

use App\Models\Visitor;
use Livewire\Component;

class VisitorForm extends Component
{
    public string $kategori = 'umum'; // 'umum' atau 'pegawai'
    public string $nama = '';
    public string $nip = '';
    public string $instansi_unit = '';
    public string $no_hp = '';
    public string $keperluan = '';

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
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();
        
        if ($this->kategori === 'umum') {
            $data['nip'] = null;
        }

        $data['kategori'] = $this->kategori;
        $visitor = Visitor::create($data);

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
        $todayVisitors = Visitor::query()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $todayPegawai = Visitor::query()
            ->whereDate('created_at', now()->toDateString())
            ->where('kategori', 'pegawai')
            ->count();

        $todayUmum = Visitor::query()
            ->whereDate('created_at', now()->toDateString())
            ->where('kategori', 'umum')
            ->count();

        return view('livewire.guest.visitor-form', [
            'todayVisitors' => $todayVisitors,
            'todayPegawai' => $todayPegawai,
            'todayUmum' => $todayUmum,
        ])->layout('layouts.guest', ['title' => 'Buku Tamu Kunjungan']);
    }
}
