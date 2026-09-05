#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
ADMIN_EMAIL="admin@mail.com"
BACKUP_DIR="$PROJECT_DIR/storage/app/backups"

cd "$PROJECT_DIR"

if [[ ! -f vendor/autoload.php ]]; then
    echo "Error: dependency Composer belum tersedia." >&2
    exit 1
fi

echo "PERINGATAN: seluruh data aplikasi di database akan dihapus."
echo "Backup SQL akan dibuat lebih dahulu di: $BACKUP_DIR"
read -r -p "Ketik RESET DATABASE untuk melanjutkan: " confirmation

if [[ "$confirmation" != "RESET DATABASE" ]]; then
    echo "Dibatalkan; database tidak diubah."
    exit 1
fi

read -r -s -p "Password baru untuk $ADMIN_EMAIL: " admin_password
echo
read -r -s -p "Ulangi password: " password_confirmation
echo

if [[ "$admin_password" != "$password_confirmation" ]]; then
    echo "Error: konfirmasi password tidak sama." >&2
    exit 1
fi

if (( ${#admin_password} < 12 )); then
    echo "Error: password minimal 12 karakter." >&2
    exit 1
fi

unset password_confirmation

echo "Membuat backup database..."
backup_output="$(php artisan database:backup --path="$BACKUP_DIR")"
echo "$backup_output"
backup_path="$(sed -n 's/^Backup dibuat: //p' <<<"$backup_output" | tail -n 1)"

if [[ -z "$backup_path" || ! -s "$backup_path" ]]; then
    echo "Error: backup tidak ditemukan atau kosong; reset dibatalkan." >&2
    exit 1
fi

backup_mode="$(stat -c '%a' "$backup_path")"
if [[ "$backup_mode" != "600" ]]; then
    echo "Error: permission backup tidak aman ($backup_mode); reset dibatalkan." >&2
    exit 1
fi

echo "Backup terverifikasi: $backup_path"
sha256sum "$backup_path"

echo "Menghapus seluruh tabel, menjalankan migrasi, dan membuat superadmin..."
SEED_ADMIN_NAME="Super Administrator" \
SEED_ADMIN_EMAIL="$ADMIN_EMAIL" \
SEED_ADMIN_PASSWORD="$admin_password" \
SEED_DEMO_DATA=false \
php artisan migrate:fresh --force --seed

echo "Memverifikasi hasil reset..."
RESET_ADMIN_EMAIL="$ADMIN_EMAIL" \
RESET_ADMIN_PASSWORD="$admin_password" \
php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = App\Models\User::query()->where("email", getenv("RESET_ADMIN_EMAIL"))->first();
$valid = $admin
    && $admin->role === "superadmin"
    && $admin->aktif
    && Illuminate\Support\Facades\Hash::check(getenv("RESET_ADMIN_PASSWORD"), $admin->password);

if (! $valid) {
    fwrite(STDERR, "Verifikasi akun superadmin gagal.\n");
    exit(1);
}

$tables = ["users", "categories", "books", "members", "visitors", "loans", "audit_logs"];
foreach ($tables as $table) {
    echo $table."=".Illuminate\Support\Facades\DB::table($table)->count().PHP_EOL;
}

echo "superadmin=verified".PHP_EOL;
'

unset admin_password
echo "Reset database selesai. Backup pemulihan: $backup_path"
