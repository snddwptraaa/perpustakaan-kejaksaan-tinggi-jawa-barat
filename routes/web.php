<?php

use App\Livewire\Admin\BookManager;
use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\LoanManager;
use App\Livewire\Admin\UserManager;
use App\Livewire\Admin\VisitorReport;
use App\Livewire\Guest\BookDetail;
use App\Livewire\Guest\CatalogBrowser;
use App\Livewire\Guest\VisitorForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome')->name('home');

Route::get('/ready', function () {
    try {
        DB::select('select 1');
        $storageReady = is_writable(Storage::disk('local')->path('.'));

        return response()->json(['status' => $storageReady ? 'ready' : 'not_ready'], $storageReady ? 200 : 503);
    } catch (Throwable) {
        return response()->json(['status' => 'not_ready'], 503);
    }
})->name('readiness');

Route::middleware('auth')->group(function (): void {
    Route::view('/profile', 'profile')->name('profile');
});
Route::get('/kunjungan', VisitorForm::class)->name('kunjungan');
Route::get('/katalog', CatalogBrowser::class)->middleware('visitor.checked')->name('katalog');
Route::get('/katalog/{book}', BookDetail::class)->middleware('visitor.checked')->name('buku.detail');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/buku', BookManager::class)->name('books');
    Route::get('/kategori', CategoryManager::class)->name('categories');
    Route::get('/peminjaman', LoanManager::class)->name('loans');
    Route::get('/pengunjung', VisitorReport::class)->name('visitors');
    Route::get('/pengguna', UserManager::class)->middleware('superadmin')->name('users');
});

require __DIR__.'/auth.php';
