<?php

use App\Livewire\Actions\Logout;
use App\Livewire\Actions\DeleteUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(DeleteUser $deleteUser, Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $error = $deleteUser->handle(Auth::id());

        if ($error) {
            $this->addError('password', $error);

            return;
        }

        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Hapus akun
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Penghapusan akun bersifat permanen. Pastikan akun ini memang tidak lagi diperlukan.
        </p>
    </header>

    <x-danger-button x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">Hapus akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">

            <h2 class="text-lg font-medium text-gray-900">
                Hapus akun Anda?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Semua data akun akan dihapus permanen. Masukkan kata sandi untuk mengonfirmasi.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Kata sandi" class="sr-only" />

                <x-text-input wire:model="password" id="password" name="password" type="password"
                    class="mt-1 block w-full" placeholder="Kata sandi" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Hapus akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
