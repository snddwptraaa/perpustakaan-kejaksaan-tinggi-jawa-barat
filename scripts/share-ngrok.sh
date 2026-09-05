#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
PORT="${NGROK_PORT:-8000}"
LARAVEL_LOG="$PROJECT_DIR/storage/logs/ngrok-laravel.log"

if ! command -v php >/dev/null 2>&1; then
    echo "Error: PHP tidak ditemukan di PATH." >&2
    exit 1
fi

if ! command -v ngrok >/dev/null 2>&1; then
    echo "Error: ngrok belum terpasang." >&2
    echo "Di CachyOS/Arch, pasang dengan: sudo pacman -S ngrok" >&2
    exit 1
fi

if [[ ! -f "$PROJECT_DIR/vendor/autoload.php" ]]; then
    echo "Error: dependency Composer belum tersedia. Jalankan: composer install" >&2
    exit 1
fi

# Laravel treats this generated marker as an instruction to use the local Vite
# development server. It is unreachable through a public ngrok HTTPS endpoint.
rm -f "$PROJECT_DIR/public/hot"

if [[ ! -f "$PROJECT_DIR/public/build/manifest.json" ]]; then
    echo "Error: aset frontend belum dibangun. Jalankan: npm ci && npm run build" >&2
    exit 1
fi

mkdir -p "$PROJECT_DIR/storage/logs"

cd "$PROJECT_DIR"
APP_DEBUG=false php artisan serve \
    --host=127.0.0.1 \
    --port="$PORT" \
    --no-reload \
    >"$LARAVEL_LOG" 2>&1 &
LARAVEL_PID=$!

cleanup() {
    if kill -0 "$LARAVEL_PID" 2>/dev/null; then
        kill "$LARAVEL_PID" 2>/dev/null || true
        wait "$LARAVEL_PID" 2>/dev/null || true
    fi
}
trap cleanup EXIT INT TERM

for _ in {1..30}; do
    if curl --fail --silent --show-error \
        --output /dev/null "http://127.0.0.1:$PORT/"; then
        break
    fi

    if ! kill -0 "$LARAVEL_PID" 2>/dev/null; then
        echo "Error: server Laravel gagal menyala. Log terakhir:" >&2
        tail -n 30 "$LARAVEL_LOG" >&2
        exit 1
    fi

    sleep 1
done

if ! curl --fail --silent --show-error \
    --output /dev/null "http://127.0.0.1:$PORT/"; then
    echo "Error: server Laravel belum siap setelah 30 detik." >&2
    echo "Periksa log: $LARAVEL_LOG" >&2
    exit 1
fi

echo "Laravel aktif di http://127.0.0.1:$PORT"
echo "Membuka tunnel ngrok; tekan Ctrl+C untuk menghentikan keduanya."
echo

if command -v systemd-inhibit >/dev/null 2>&1; then
    echo "Sleep inhibitor aktif selama tunnel berjalan."
    systemd-inhibit \
        --what=sleep:idle:handle-lid-switch \
        --who="Perpustakaan ngrok development server" \
        --why="Menjaga Laravel, ngrok, dan layanan lokal tetap aktif" \
        --mode=block \
        ngrok http "$PORT"
else
    echo "Peringatan: systemd-inhibit tidak tersedia; suspend tidak dapat dicegah." >&2
    ngrok http "$PORT"
fi
