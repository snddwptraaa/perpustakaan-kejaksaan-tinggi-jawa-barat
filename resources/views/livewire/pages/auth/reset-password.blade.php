<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div
    class="flex min-h-[calc(100vh-4.5rem)] items-center justify-center bg-gradient-to-br from-stone-50 via-emerald-50/30 to-stone-100 px-4 py-12 sm:px-6">
    <div class="w-full max-w-md">
        <!-- Card Container -->
        <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-xl shadow-kejati-dark/5">

            <div class="bg-gradient-to-br from-kejati-dark to-kejati px-8 py-8 text-center">
                <h1 class="text-xl font-bold text-white">Buat Kata Sandi Baru</h1>
                <p class="mt-1 text-sm text-white/70">Gunakan kata sandi yang kuat dan mudah Anda ingat.</p>
            </div>

            <!-- Card Body: Form -->
            <div class="px-8 py-8">
                <form wire:submit="resetPassword" class="space-y-5">
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Email Akun</label>
                        <input wire:model="email" id="email" type="email" name="email" required autofocus
                            autocomplete="username"
                            class="mt-1.5 block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi Baru</label>
                        <input wire:model="password" id="password" type="password" name="password" required
                            autocomplete="new-password" placeholder="••••••••"
                            class="mt-1.5 block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Konfirmasi
                            Kata Sandi Baru</label>
                        <input wire:model="password_confirmation" id="password_confirmation" type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"
                            class="mt-1.5 block w-full rounded-xl border-stone-300 bg-stone-50/50 px-4 py-2.5 text-sm shadow-sm transition focus:border-kejati focus:bg-white focus:ring-kejati" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-kejati px-5 py-3.5 text-sm font-bold text-white shadow-md transition duration-200 hover:bg-kejati-dark hover:shadow-lg disabled:cursor-wait disabled:opacity-75">
                        <!-- Loading spinner -->
                        <svg wire:loading wire:target="resetPassword" class="h-4 w-4 shrink-0 animate-spin text-white"
                            viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>

                        <!-- Button label -->
                        <span wire:loading.remove wire:target="resetPassword">Simpan Kata Sandi Baru</span>
                        <span wire:loading wire:target="resetPassword">Memproses…</span>

                        <!-- Arrow icon -->
                        <svg wire:loading.remove wire:target="resetPassword" class="h-4 w-4 shrink-0" fill="none"
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
