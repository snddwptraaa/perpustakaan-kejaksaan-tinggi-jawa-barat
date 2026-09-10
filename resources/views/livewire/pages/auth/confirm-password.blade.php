<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (
            !Auth::guard('web')->validate([
                'email' => Auth::user()->email,
                'password' => $this->password,
            ])
        ) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div
    class="flex min-h-[calc(100vh-4.5rem)] items-center justify-center bg-gradient-to-br from-stone-50 via-emerald-50/30 to-stone-100 px-4 py-12 sm:px-6">
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-xl shadow-kejati-dark/5">
        <div class="bg-gradient-to-br from-kejati-dark to-kejati px-8 py-8 text-center">
            <h1 class="text-xl font-bold text-white">Konfirmasi Kata Sandi</h1>
            <p class="mt-1 text-sm text-white/70">Verifikasi akun sebelum melanjutkan.</p>
        </div>

        <form wire:submit="confirmPassword" class="space-y-5 px-8 py-8">
            <p class="text-sm leading-6 text-slate-600">
                Area ini memuat tindakan sensitif. Masukkan kata sandi akun Anda untuk melanjutkan.
            </p>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700">Kata sandi</label>
                <input wire:model="password" id="password" class="field-control mt-1.5" type="password"
                    name="password" required autocomplete="current-password" aria-describedby="password-error"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-2" />
            </div>

            <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full">
                <span wire:loading.remove wire:target="confirmPassword">Konfirmasi dan Lanjutkan</span>
                <span wire:loading wire:target="confirmPassword">Memverifikasi…</span>
            </button>
        </form>
    </div>
</div>
