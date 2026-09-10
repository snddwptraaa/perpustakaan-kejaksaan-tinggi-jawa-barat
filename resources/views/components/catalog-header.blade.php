<header class="catalog-masthead">
    <div class="catalog-container flex flex-wrap items-center justify-between gap-4 py-5">
        <a href="{{ route('katalog') }}" class="flex min-w-0 items-center gap-3 rounded-lg" wire:navigate>
            <img src="{{ asset('images/logo.svg') }}" alt="" width="40" height="46" class="h-11 w-10 shrink-0 object-contain">
            <span>
                <span class="block text-sm font-bold text-kejati-dark">Perpustakaan</span>
                <span class="mt-0.5 block text-xs leading-5 text-slate-600">Kejaksaan Tinggi Jawa Barat</span>
            </span>
        </a>
        @if (session('visitor_checked_in'))
            <form action="{{ route('kunjungan.selesai') }}" method="POST">
                @csrf
                <button type="submit" class="catalog-exit" title="Akhiri sesi dan buka formulir untuk pengunjung berikutnya">
                    Selesai kunjungan
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H5v14h4m6-12 5 5-5 5M9 12h11" /></svg>
                </button>
            </form>
        @endif
    </div>
</header>
