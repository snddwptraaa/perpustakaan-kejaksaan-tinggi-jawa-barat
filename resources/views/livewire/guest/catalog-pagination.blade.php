@if ($paginator->hasPages())
    <nav aria-label="Halaman katalog" class="catalog-pagination" x-on:click="if ($event.target.closest('button:not(:disabled)')) document.getElementById('catalog-title').scrollIntoView({ block: 'start' })">
        <p class="text-sm text-slate-600">Halaman <strong class="text-slate-800">{{ $paginator->currentPage() }}</strong> dari {{ $paginator->lastPage() }}</p>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled($paginator->onFirstPage()) class="catalog-page-button" aria-label="Halaman sebelumnya"><span aria-hidden="true">←</span></button>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-slate-600">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        <button wire:key="catalog-page-{{ $page }}" type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="catalog-page-button" aria-label="Halaman {{ $page }}" @if ($page === $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</button>
                    @endforeach
                @endif
            @endforeach
            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages()) class="catalog-page-button" aria-label="Halaman berikutnya"><span aria-hidden="true">→</span></button>
        </div>
    </nav>
@endif
