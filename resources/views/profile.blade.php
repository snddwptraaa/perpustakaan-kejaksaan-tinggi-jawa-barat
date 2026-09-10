<x-admin-layout title="Pengaturan akun">
    <div class="page-shell max-w-3xl">
        <div>
            <h1 class="page-title mt-0">Pengaturan akun</h1>
            <p class="page-description">Perbarui identitas akun dan kata sandi Anda.</p>
        </div>

        <div class="surface p-6 sm:p-8">
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="surface p-6 sm:p-8">
            <livewire:profile.update-password-form />
        </div>

        <details class="surface overflow-hidden">
            <summary class="cursor-pointer px-6 py-5 text-sm font-semibold text-rose-700">Tindakan akun lanjutan</summary>
            <div class="border-t border-stone-200 p-6 sm:p-8">
                <livewire:profile.delete-user-form />
            </div>
        </details>
    </div>
</x-admin-layout>
