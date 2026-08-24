<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div
    class="flex min-h-[calc(100vh-4.5rem)] items-center justify-center bg-gradient-to-br from-stone-50 via-emerald-50/30 to-stone-100 px-4 py-12 sm:px-6">
    <div class="w-full max-w-md">
        <!-- Card Container -->
        <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-xl shadow-kejati-dark/5">

            <!-- Card Header with Logo -->
            <div class="bg-gradient-to-br from-kejati-dark to-kejati px-8 pb-8 pt-10 text-center">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-white/20 bg-white/10 shadow-lg backdrop-blur">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo Kejaksaan"
                        class="h-10 w-auto object-contain drop-shadow">
                </div>
                <h1 class="text-xl font-bold text-white">Lupa Kata Sandi?</h1>
                <p class="mt-1 text-sm text-white/70">Perpustakaan Digital Kejati Jawa Barat</p>
            </div>

            <!-- Card Body: Form -->
            <div class="px-8 py-8">
                <!-- Info Message -->
                <div
                    class="mb-5 rounded-xl border border-emerald-100 bg-emerald-50/70 p-3.5 text-xs leading-relaxed text-emerald-950">
                    Masukkan alamat email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi baru.
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form wire:submit="sendPasswordResetLink" class="space-y-5">
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Email Akun</label>
                        <input wire:model="email" id="email" type="email" name="email" required autofocus
                            autocomplete="username" placeholder="contoh@perpustakaan.kejati-jabar.go.id"
                            class="mt-1.5 block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-kejati px-5 py-3.5 text-sm font-bold text-white shadow-md transition duration-200 hover:bg-kejati-dark hover:shadow-lg disabled:cursor-wait disabled:opacity-75">
                        <!-- Loading spinner -->
                        <svg wire:loading wire:target="sendPasswordResetLink"
                            class="h-4 w-4 shrink-0 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>

                        <!-- Button label -->
                        <span wire:loading.remove wire:target="sendPasswordResetLink">Kirim Tautan Reset Password</span>
                        <span wire:loading wire:target="sendPasswordResetLink">Mengirim tautan…</span>

                        <!-- Arrow icon -->
                        <svg wire:loading.remove wire:target="sendPasswordResetLink" class="h-4 w-4 shrink-0"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>

                <!-- Navigation Links -->
                <div
                    class="mt-6 flex items-center justify-between border-t border-stone-100 pt-5 text-xs font-medium text-slate-500">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-1 text-kejati transition hover:text-kejati-dark hover:underline"
                        wire:navigate>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Kembali ke Masuk
                    </a>
                    <a href="{{ route('home') }}" class="text-slate-500 transition hover:text-slate-800 hover:underline"
                        wire:navigate>
                        Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>