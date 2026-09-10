<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('admin.dashboard', absolute: false));
    }
}; ?>

<div
    class="flex min-h-[calc(100vh-4.5rem)] items-center justify-center bg-gradient-to-br from-stone-50 via-emerald-50/30 to-stone-100 px-4 py-12 sm:px-6">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-xl shadow-kejati-dark/5">

            <div class="bg-gradient-to-br from-kejati-dark to-kejati px-8 py-8 text-center">
                <h1 class="text-xl font-bold text-white">Masuk petugas</h1>
                <p class="mt-1 text-sm text-white/70">Kelola koleksi, sirkulasi, dan kunjungan.</p>
            </div>

            <!-- Card Body: Form -->
            <div class="px-8 py-8">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form wire:submit="login" class="space-y-5">
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                        <input wire:model="form.email" id="email" aria-describedby="email-error" aria-invalid="{{ $errors->has('form.email') ? 'true' : 'false' }}" type="email" name="email" required autofocus
                            autocomplete="username" placeholder="Alamat email akun petugas"
                            class="mt-1.5 block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati" />
                        <x-input-error id="email-error" :messages="$errors->get('form.email')" class="mt-1.5" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Kata sandi</label>
                        <div x-data="{ reveal: false }" class="relative mt-1.5">
                            <input wire:model="form.password" id="password" :type="reveal ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi" class="field-control py-3 pr-24" aria-describedby="password-error" aria-invalid="{{ $errors->has('form.password') ? 'true' : 'false' }}">
                            <button type="button" @click="reveal = !reveal" :aria-pressed="reveal" aria-controls="password" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-kejati" x-text="reveal ? 'Sembunyikan' : 'Tampilkan'">Tampilkan</button>
                        </div>
                        <x-input-error id="password-error" :messages="$errors->get('form.password')" class="mt-1.5" />
                    </div>

                    <!-- Remember Me & Forgot -->
                    <div class="flex items-center justify-between">
                        <label for="remember" class="inline-flex items-center cursor-pointer">
                            <input wire:model="form.remember" id="remember" type="checkbox"
                                class="rounded border-stone-300 text-kejati shadow-sm focus:ring-kejati"
                                name="remember">
                            <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-xs font-medium text-kejati transition hover:text-kejati-dark hover:underline"
                                href="{{ route('password.request') }}" wire:navigate>
                                Lupa kata sandi?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-kejati px-5 py-3.5 text-sm font-bold text-white shadow-md transition duration-200 hover:bg-kejati-dark hover:shadow-lg disabled:cursor-wait disabled:opacity-75">
                        <!-- Loading spinner -->
                        <svg wire:loading wire:target="login" class="h-4 w-4 shrink-0 animate-spin text-white"
                            viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>

                        <!-- Button label -->
                        <span wire:loading.remove wire:target="login">Masuk</span>
                        <span wire:loading wire:target="login">Memproses login…</span>

                        <!-- Arrow icon -->
                        <svg wire:loading.remove wire:target="login" class="h-4 w-4 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
