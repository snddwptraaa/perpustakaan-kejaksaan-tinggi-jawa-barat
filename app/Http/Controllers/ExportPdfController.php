<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Visitor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportPdfController extends Controller
{
    /**
     * Export katalog buku ke PDF.
     */
    public function catalog(Request $request)
    {
        $query = Book::query()->where('archived_at', null);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('penulis', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        $books = $query->with('category')->orderBy('judul')->take(1000)->get();

        $pdf = Pdf::loadView('reports.catalog-pdf', compact('books'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('katalog-buku-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Export laporan pengunjung ke PDF.
     */
    public function visitors(Request $request)
    {
        $query = Visitor::query();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $visitors = $query->orderByDesc('created_at')->take(1000)->get();
        $from = $request->input('from');
        $to = $request->input('to');

        $suffix = ($from && $to) ? "-{$from}-sd-{$to}" : (($from) ? "-sejak-{$from}" : (($to) ? "-sampai-{$to}" : ''));

        $pdf = Pdf::loadView('reports.visitors-pdf', compact('visitors', 'from', 'to'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-pengunjung'.$suffix.'-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Export laporan peminjaman ke PDF.
     */
    public function loans(Request $request)
    {
        $query = Loan::with(['book', 'member']);

        if ($request->filled('from')) {
            $query->whereDate('tanggal_pinjam', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('tanggal_pinjam', '<=', $request->input('to'));
        }

        if ($request->filled('status') && $request->input('status') !== 'semua') {
            $status = $request->input('status');
            if ($status === 'terlambat') {
                $query->whereNull('tanggal_kembali')
                    ->whereNull('tanggal_dibatalkan')
                    ->whereDate('tanggal_jatuh_tempo', '<', today());
            } elseif ($status === 'dipinjam') {
                $query->whereNull('tanggal_kembali')
                    ->whereNull('tanggal_dibatalkan')
                    ->whereDate('tanggal_jatuh_tempo', '>=', today());
            } elseif ($status === 'dikembalikan') {
                $query->whereNotNull('tanggal_kembali');
            } elseif ($status === 'dibatalkan') {
                $query->whereNotNull('tanggal_dibatalkan');
            }
        }

        $loans = $query->orderByDesc('tanggal_pinjam')->take(1000)->get();
        $from = $request->input('from');
        $to = $request->input('to');
        $statusFilter = $request->input('status', 'semua');

        $suffix = ($from && $to) ? "-{$from}-sd-{$to}" : (($from) ? "-sejak-{$from}" : (($to) ? "-sampai-{$to}" : ''));

        $pdf = Pdf::loadView('reports.loans-pdf', compact('loans', 'from', 'to', 'statusFilter'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-peminjaman'.$suffix.'-'.now()->format('Y-m-d').'.pdf');
    }
}
