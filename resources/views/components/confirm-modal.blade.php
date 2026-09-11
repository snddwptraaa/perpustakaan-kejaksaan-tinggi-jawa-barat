<div x-data="{
    open: false,
    title: 'Konfirmasi Tindakan',
    message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    confirmButtonText: 'Ya, Lanjutkan',
    cancelButtonText: 'Batal',
    type: 'danger',
    callback: null,
    showConfirm(detail) {
        this.title = detail.title || 'Konfirmasi Tindakan';
        this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        this.confirmButtonText = detail.confirmButtonText || (detail.type === 'danger' ? 'Ya, Hapus Data' : 'Ya, Lanjutkan');
        this.cancelButtonText = detail.cancelButtonText || 'Batal';
        this.type = detail.type || 'danger';
        this.callback = detail.onConfirm;
        this.open = true;
    },
    confirm() {
        if (typeof this.callback === 'function') {
            this.callback();
        }
        this.open = false;
    },
    cancel() {
        this.open = false;
    }
}"
@open-confirm-modal.window="showConfirm($event.detail)"
x-cloak
x-show="open"
x-trap.inert.noscroll="open"
@keydown.escape.window="if (open) cancel()"
class="fixed inset-0 z-[100] overflow-y-auto"
aria-labelledby="modal-confirm-title"
aria-describedby="modal-confirm-description"
role="dialog"
aria-modal="true">

    <!-- Backdrop Overlay with Dark Blur -->
    <div x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity"
        @click="cancel"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
        <!-- Dialog Card -->
        <div x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative my-8 w-full max-w-md transform overflow-hidden rounded-3xl border border-stone-200/80 bg-white p-6 text-left shadow-2xl transition sm:p-8">
            
            <div class="flex items-start gap-4">
                <!-- Icon Badge -->
                <div :class="type === 'danger' ? 'bg-rose-100 text-rose-600 ring-8 ring-rose-50' : 'bg-amber-100 text-amber-600 ring-8 ring-amber-50'"
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                    <template x-if="type === 'danger'">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </template>
                    <template x-if="type !== 'danger'">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>
                </div>

                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-slate-900 leading-snug" id="modal-confirm-title" x-text="title"></h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600" id="modal-confirm-description" x-text="message"></p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex items-center justify-end gap-3 border-t border-stone-100 pt-5">
                <button type="button"
                    @click="cancel"
                    class="rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-stone-50 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-kejati focus-visible:ring-offset-2"
                    x-text="cancelButtonText">
                </button>
                <button type="button"
                    @click="confirm"
                    :class="type === 'danger' ? 'bg-rose-700 hover:bg-rose-800 shadow-rose-700/20' : 'bg-kejati hover:bg-kejati-dark shadow-kejati/20'"
                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold text-white shadow-lg transition focus-visible:ring-2 focus-visible:ring-kejati focus-visible:ring-offset-2"
                    x-text="confirmButtonText">
                </button>
            </div>

        </div>
    </div>
</div>
