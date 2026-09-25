<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminProtectionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_last_admin_cannot_remove_own_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->from(route('admin.index'))->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'is_admin' => false, '_admin_tab' => 'admin-users',
        ])->assertSessionHasErrors(['is_admin' => 'Debe quedar al menos un administrador con una cuenta activa.'])
            ->assertSessionHasInput('_admin_tab', 'admin-users');
        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_pending_admin_does_not_allow_last_active_admin_to_be_demoted(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['is_admin' => true, 'is_registered' => false]);
        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'is_admin' => false,
        ])->assertSessionHasErrors('is_admin');
        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_admin_can_be_demoted_when_another_active_admin_remains(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $other = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->put(route('admin.users.update', $other), [
            'name' => $other->name, 'email' => $other->email, 'is_admin' => false,
        ])->assertSessionHasNoErrors();
        $this->assertFalse($other->fresh()->is_admin);
        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_last_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertStatus(422);
        $this->assertModelExists($admin);
    }

    public function test_validation_errors_render_with_the_correct_panel_visible(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->from(route('admin.index'))->post(route('admin.courses.store'), ['_admin_tab' => 'admin-courses'])
            ->assertSessionHasErrors(['title', 'description']);
        $this->assertSame('admin-courses', session('_old_input._admin_tab'));
    }

    public function test_flashed_validation_errors_are_visible_in_the_submitted_panel(): void
    {
        $errors = ['default' => ['format' => ':message', 'messages' => ['title' => ['Introduce el título del curso.']]]];
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->withSession(['errors' => $errors, '_old_input' => ['_admin_tab' => 'admin-courses']])
            ->get(route('admin.index'))->assertViewHas('errors', fn ($bag): bool => $bag->any())->assertSee('No se han guardado los cambios:')
            ->assertSee('id="admin-courses" class="admin-panel is-active"', false);
    }

    public function test_pagination_preserves_users_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(16)->create();
        $this->actingAs($admin)->get(route('admin.index', ['page' => 2, 'tab' => 'admin-users']))
            ->assertSee('id="admin-users" class="admin-panel is-active"', false)
            ->assertSee('tab=admin-users', false);
    }
}
