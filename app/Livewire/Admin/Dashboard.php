<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Visitor;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'totalBooks' => Book::count(),
            'availableCopies' => Book::sum('stok_tersedia'),
            'activeLoans' => Loan::active()->count(),
            'todayVisitors' => Visitor::whereDate('created_at', today())->count(),
            'recentLoans' => Loan::with('book')->latest()->limit(5)->get(),
        ])->layout('layouts.admin', ['title' => 'Ringkasan']);
    }
}
