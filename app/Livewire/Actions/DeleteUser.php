<?php

namespace App\Livewire\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUser
{
    public function handle(int $userId, ?int $authenticatedUserId = null): ?string
    {
        return DB::transaction(function () use ($userId, $authenticatedUserId): ?string {
            if ($authenticatedUserId !== null) {
                $actor = User::query()->lockForUpdate()->findOrFail($authenticatedUserId);

                if (! $actor->isSuperadmin()) {
                    return 'Hanya superadmin yang dapat menghapus akun lain.';
                }
            }

            $superadminIds = User::query()
                ->where('role', 'superadmin')
                ->lockForUpdate()
                ->pluck('id');

            $user = User::query()->withCount(['loans', 'cancelledLoans'])->lockForUpdate()->findOrFail($userId);

            if ($authenticatedUserId !== null && $user->id === $authenticatedUserId) {
                return 'Akun yang sedang digunakan tidak dapat dihapus dari halaman manajemen pengguna.';
            }

            if ($user->isSuperadmin() && $superadminIds->count() <= 1) {
                return 'Superadmin terakhir tidak dapat dihapus.';
            }

            if ($user->loans_count > 0 || $user->cancelled_loans_count > 0) {
                return 'Akun tidak dapat dihapus karena memiliki riwayat transaksi peminjaman.';
            }

            $user->delete();

            return null;
        }, attempts: 3);
    }
}
