<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class MemberHistory extends Component
{
    use WithPagination;

    public string $search = '';

    public string $member_id = '';

    public string $book_id = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'member_id', 'book_id'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        return view('livewire.admin.member-history', [
            'loans' => Loan::with(['book', 'member', 'petugas'])
                ->when($this->search !== '', fn ($query) => $query->where('nama_peminjam', 'like', "%{$this->search}%"))
                ->when($this->member_id !== '', fn ($query) => $query->where('member_id', $this->member_id))
                ->when($this->book_id !== '', fn ($query) => $query->where('book_id', $this->book_id))
                ->latest()->paginate(15),
            'members' => Cache::remember('members:options', now()->addMinutes(5), fn () => Member::active()->orderBy('nama')->limit(200)->get()),
            'books' => Cache::remember('books:options', now()->addMinutes(5), fn () => Book::active()->orderBy('judul')->limit(200)->get()),
        ])->layout('layouts.admin', ['title' => 'Riwayat Sirkulasi']);
    }
}
