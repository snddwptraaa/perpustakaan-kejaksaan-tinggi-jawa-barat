<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UserManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    }

    public function test_superadmin_can_access_users_page(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.users'));
        $response->assertOk();
    }

    public function test_regular_admin_cannot_access_users_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users'))
            ->assertForbidden();
    }

    public function test_can_open_create_user_modal(): void
    {
        Livewire::actingAs($this->superadmin)
            ->test(UserManager::class)
            ->assertSet('showForm', false)
            ->call('create')
            ->assertSet('showForm', true)
            ->call('resetForm')
            ->assertSet('showForm', false);
    }

    public function test_can_create_new_admin_user(): void
    {
        Livewire::actingAs($this->superadmin)
            ->test(UserManager::class)
            ->call('create')
            ->set('name', 'Petugas Baru')
            ->set('email', 'petugas.baru@kejati.test')
            ->set('password', 'password123')
            ->set('role', 'admin')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('users', [
            'name' => 'Petugas Baru',
            'email' => 'petugas.baru@kejati.test',
            'role' => 'admin',
        ]);
    }

    public function test_validation_rules_for_creating_user(): void
    {
        Livewire::actingAs($this->superadmin)
            ->test(UserManager::class)
            ->call('create')
            ->set('name', '')
            ->set('email', 'not-an-email')
            ->set('password', '123')
            ->call('save')
            ->assertHasErrors(['name', 'email', 'password']);
    }

    public function test_superadmin_can_edit_user_and_optionally_reset_password(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@kejati.test',
            'role' => 'admin',
        ]);

        Livewire::actingAs($this->superadmin)
            ->test(UserManager::class)
            ->call('edit', $targetUser->id)
            ->set('name', 'Nama Baru')
            ->set('email', 'baru@kejati.test')
            ->set('role', 'superadmin')
            ->set('password', 'password-baru')
            ->call('save')
            ->assertHasNoErrors();

        $targetUser->refresh();
        $this->assertSame('Nama Baru', $targetUser->name);
        $this->assertSame('baru@kejati.test', $targetUser->email);
        $this->assertSame('superadmin', $targetUser->role);
        $this->assertTrue(Hash::check('password-baru', $targetUser->password));
    }

    public function test_editing_user_without_password_keeps_existing_password(): void
    {
        $targetUser = User::factory()->create();
        $password = $targetUser->password;

        Livewire::actingAs($this->superadmin)
            ->test(UserManager::class)
            ->call('edit', $targetUser->id)
            ->set('name', 'Nama Dikoreksi')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($password, $targetUser->fresh()->password);
    }

    public function test_can_search_users(): void
    {
        $user1 = User::factory()->create(['name' => 'Budi Sudarsono', 'email' => 'budi@test.com']);
        $user2 = User::factory()->create(['name' => 'Siti Aminah', 'email' => 'siti@test.com']);

        Livewire::actingAs($this->superadmin)
            ->test(UserManager::class)
            ->set('search', 'Budi')
            ->assertSee('Budi Sudarsono')
            ->assertDontSee('Siti Aminah');
    }

    public function test_can_delete_other_user(): void
    {
        $targetUser = User::factory()->create(['name' => 'User Hapus']);

        Livewire::actingAs($this->superadmin)
            ->test(UserManager::class)
            ->call('delete', $targetUser->id);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_cannot_delete_self(): void
    {
        Livewire::actingAs($this->superadmin)
            ->test(UserManager::class)
            ->call('delete', $this->superadmin->id);

        $this->assertDatabaseHas('users', ['id' => $this->superadmin->id]);
    }

    public function test_regular_admin_cannot_invoke_user_deletion_action(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($otherAdmin)
            ->test(UserManager::class)
            ->call('delete', $this->superadmin->id)
            ->assertSee('Hanya superadmin yang dapat menghapus akun lain.');

        $this->assertDatabaseHas('users', ['id' => $this->superadmin->id]);
    }
}
