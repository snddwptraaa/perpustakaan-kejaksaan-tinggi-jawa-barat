@props([
    'placeholder' => '-- Pilih Opsi --',
    'options' => [],
    'id' => null,
    'required' => false,
    'error' => null,
    'searchable' => null,
    'disabled' => false,
    // Label untuk nilai kosong ('') yang bisa dipilih ulang, mis. "Semua kategori".
    'emptyOption' => null,
])

@php
    $wireModel = $attributes->wire('model');
    $wireModelName = $wireModel ? $wireModel->value() : null;
    // Modifier .live mengirim perubahan segera; tanpa modifier, pertahankan
    // semantik deferred seperti wire:model pada kontrol formulir biasa.
    $liveUpdate = $wireModel && $wireModel->modifiers()->contains('live');
    $hasError = ! empty($error);

    // Terima Collection maupun array (Eloquent, paginate result, dsb).
    $options = is_iterable($options) ? collect($options)->all() : $options;

    // Normalisasi opsi ke format array asosiatif: [['value' => '...', 'label' => '...']]
    $normalizedOptions = [];
    $isAssoc = $options !== [] && array_keys($options) !== range(0, count($options) - 1);

    foreach ($options as $key => $option) {
        if (is_array($option)) {
            $normalizedOptions[] = [
                'value' => (string) ($option['value'] ?? $key),
                'label' => (string) ($option['label'] ?? $option['name'] ?? $key),
            ];
        } elseif (is_object($option)) {
            // Dukungan Eloquent/Collection: id sebagai value, properti label/nama/judul sebagai teks.
            $label = $option->label
                ?? $option->nama
                ?? $option->judul
                ?? $option->nama_kategori
                ?? $option->name
                ?? (string) $key;
            $normalizedOptions[] = [
                'value' => (string) ($option->id ?? $key),
                'label' => (string) $label,
            ];
        } elseif ($isAssoc) {
            $normalizedOptions[] = [
                'value' => (string) $key,
                'label' => (string) $option,
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string) $option,
                'label' => (string) $option,
            ];
        }
    }

    $isSearchable = $searchable ?? (count($normalizedOptions) >= 5);
    if ($emptyOption !== null) {
        array_unshift($normalizedOptions, ['value' => '', 'label' => (string) $emptyOption]);
    }
    $wireKey = $attributes->get('wire:key', 'custom-select-' . ($id ?? uniqid()) . '-' . ($wireModelName ?? 'field') . '-' . count($normalizedOptions));
@endphp

<div
    wire:key="{{ $wireKey }}"
    data-component="custom-select"
    x-data="{
        open: false,
        search: '',
        value: @if($wireModelName) $wire.get('{{ $wireModelName }}') @else '' @endif,
        options: @js($normalizedOptions),
        placeholder: @js($placeholder),
        highlightedIndex: -1,
        init() {
            @if($wireModelName)
                this.$watch('$wire.{{ $wireModelName }}', (newVal) => {
                    this.value = newVal;
                });
            @endif
        },
        get selectedOption() {
            return this.options.find(opt => String(opt.value) === String(this.value));
        },
        get displayLabel() {
            return this.selectedOption ? this.selectedOption.label : this.placeholder;
        },
        get filteredOptions() {
            if (!this.search || !this.search.trim()) {
                return this.options;
            }
            let q = this.search.toLowerCase().trim();
            return this.options.filter(opt => opt.label.toLowerCase().includes(q));
        },
        select(val) {
            this.value = val;
            this.open = false;
            this.search = '';
            @if($wireModelName)
                $wire.set('{{ $wireModelName }}', val, {{ $liveUpdate ? 'true' : 'false' }});
            @endif
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.search = '';
                this.highlightedIndex = -1;
                @if($isSearchable)
                    this.$nextTick(() => {
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                @endif
            }
        },
        close() {
            this.open = false;
            this.search = '';
            this.highlightedIndex = -1;
        },
        highlightNext() {
            if (!this.open) {
                this.toggle();
                return;
            }
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            } else {
                this.highlightedIndex = 0;
            }
        },
        highlightPrev() {
            if (!this.open) {
                this.toggle();
                return;
            }
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            } else {
                this.highlightedIndex = this.filteredOptions.length - 1;
            }
        },
        selectHighlighted() {
            if (this.open && this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                this.select(this.filteredOptions[this.highlightedIndex].value);
            } else if (!this.open) {
                this.toggle();
            }
        }
    }"
    class="relative"
>
    <!-- Hidden input for standard forms and accessibility -->
    <input
        type="hidden"
        @if($id) id="{{ $id }}-hidden" @endif
        @if($wireModelName) name="{{ $wireModelName }}" @endif
        :value="value"
        @if($required) required @endif
    />

    <!-- Trigger Button -->
    <button
        type="button"
        @if($id) id="{{ $id }}" @endif
        x-ref="trigger"
        @click="toggle()"
        @keydown.down.prevent="highlightNext()"
        @keydown.up.prevent="highlightPrev()"
        @keydown.enter.prevent="selectHighlighted()"
        @keydown.escape.prevent="close()"
        aria-haspopup="listbox"
        :aria-expanded="open"
        @if($disabled) disabled @endif
        {{ $attributes->except(array_filter([$wireModel?->directive(), 'wire:model', 'wire:key'])) }}
        class="group flex w-full items-center justify-between gap-3 select-trigger{{ $hasError ? ' border-rose-300 bg-rose-50/30 text-rose-900 focus:border-rose-500 focus:ring-rose-200' : '' }}"
    >
        <span
            class="truncate"
            :class="selectedOption ? 'text-slate-900 font-medium' : 'text-slate-400'"
            x-text="displayLabel"
        >
            {{ $placeholder }}
        </span>

        <span class="flex items-center gap-1.5 text-slate-400 group-hover:text-slate-600 transition-colors">
            <svg
                aria-hidden="true"
                class="h-4 w-4 shrink-0 transition-transform duration-200"
                :class="{ 'rotate-180 text-kejati': open }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-98"
        @click.outside="close()"
        @keydown.escape.window="close()"
        class="select-panel"
        role="listbox"
        @if($id) aria-labelledby="{{ $id }}" @endif
        style="display: none;"
    >
        @if($isSearchable)
            <div class="mb-1.5 border-b border-stone-100 pb-1.5 px-1">
                <div class="relative flex items-center">
                    <svg aria-hidden="true" class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        x-ref="searchInput"
                        x-model="search"
                        type="text"
                        aria-label="Cari pilihan"
                        placeholder="Ketik untuk mencari…"
                        class="select-search"
                        @keydown.down.prevent="highlightNext()"
                        @keydown.up.prevent="highlightPrev()"
                        @keydown.enter.prevent="selectHighlighted()"
                        @keydown.escape.stop="close()"
                    />
                    <button
                        type="button"
                        x-show="search.length > 0"
                        @click="search = ''"
                        aria-label="Hapus pencarian pilihan"
                        class="absolute right-2 text-slate-400 hover:text-slate-600"
                    >
                        <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- Options Container -->
        <div class="max-h-56 overflow-y-auto space-y-0.5 p-0.5" tabindex="-1">
            <template x-for="(opt, idx) in filteredOptions" :key="opt.value">
                <button
                    type="button"
                    @click="select(opt.value)"
                    @mouseenter="highlightedIndex = idx"
                    class="select-option"
                    :class="{
                        'bg-kejati/[0.07] text-slate-900': highlightedIndex === idx && String(opt.value) !== String(value)
                    }"
                    role="option"
                    :aria-selected="String(opt.value) === String(value)"
                >
                    <span class="truncate" x-text="opt.label"></span>
                    <svg
                        aria-hidden="true"
                        x-show="String(opt.value) === String(value)"
                        class="h-4 w-4 shrink-0 text-kejati ml-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </template>

            <!-- Empty Search State -->
            <div
                x-show="filteredOptions.length === 0"
                class="select-empty"
            >
                Tidak ada pilihan yang cocok.
            </div>
        </div>
    </div>
</div>
