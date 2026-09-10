<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Visitor;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stats = Cache::remember('admin:dashboard-stats', now()->addSeconds(60), function (): array {
            return [
                'totalBooks' => Book::active()->count(),
                'totalCopies' => (int) Book::sum('stok'),
                'availableCopies' => (int) Book::sum('stok_tersedia'),
                'activeLoans' => Loan::active()->count(),
                'overdueLoans' => Loan::active()->whereDate('tanggal_jatuh_tempo', '<', today())->count(),
                'todayVisitors' => Visitor::whereDate('created_at', today())->count(),
            ];
        });

        return view('livewire.admin.dashboard', [
            ...$stats,
            'recentLoans' => Loan::with('book')->latest()->limit(5)->get(),
        ])->layout('layouts.admin', ['title' => 'Ringkasan']);
    }
}
