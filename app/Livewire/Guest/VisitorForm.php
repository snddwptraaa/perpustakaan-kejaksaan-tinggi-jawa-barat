<?php

namespace App\Livewire\Guest;

use App\Models\Visitor;
use Livewire\Component;

class VisitorForm extends Component
{
    public string $nama = '';
    public string $nip = '';
    public string $instansi_unit = '';
    public string $no_hp = '';
    public string $keperluan = '';

    protected function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:30'],
            'instansi_unit' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'keperluan' => ['required', 'string', 'max:255'],
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();
        $visitor = Visitor::create($data);

        session([
            'visitor_checked_in' => true,
            'visitor_id' => $visitor->id,
            'visitor_checked_in_at' => now()->toDateString(),
        ]);

        $this->redirectRoute('katalog');
    }

    public function render()
    {
        $todayVisitors = Visitor::query()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return view('livewire.guest.visitor-form', [
            'todayVisitors' => $todayVisitors,
        ])->layout('layouts.guest', ['title' => 'Isi Data Kunjungan']);
    }
}
