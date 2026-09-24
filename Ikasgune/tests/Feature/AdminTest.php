<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_must_log_in_before_accessing_admin(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('login'));
    }

    public function test_normal_users_are_forbidden_from_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.index'))->assertForbidden();
        $this->assertFalse(Gate::forUser($user)->allows('access-admin'));
    }

    public function test_normal_users_do_not_see_admin_navigation(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertDontSee(route('admin.index'));
    }

    public function test_admins_see_real_counts_and_escaped_paginated_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(15)->create();
        $newest = User::factory()->create(['name' => '<script>alert(1)</script>']);

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()->assertViewHas('totalUsers', 17)->assertViewHas('totalAdmins', 1)
            ->assertViewHas('users', fn ($users): bool => $users->count() === 15 && $users->total() === 17)
            ->assertSee($newest->name)->assertDontSee($newest->name, false);
        $this->assertTrue(Gate::forUser($admin)->allows('access-admin'));
    }

    public function test_admins_can_open_the_second_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(15)->create();

        $this->actingAs($admin)->get(route('admin.index', ['page' => 2]))
            ->assertOk()->assertSee($admin->email)
            ->assertViewHas('users', fn ($users): bool => $users->count() === 1);
    }

    public function test_admin_login_redirects_to_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.index'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_command_promotes_only_the_selected_existing_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->artisan('ikasgune:make-admin', ['email' => $user->email])
            ->expectsOutput('La cuenta ya tiene acceso a administración.')->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
        $this->assertFalse($other->fresh()->is_admin);
    }

    public function test_admin_login_goes_to_admin_even_after_requesting_another_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->withSession(['url.intended' => route('dashboard')])
            ->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.index'))
            ->assertSessionMissing('url.intended');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_command_rejects_unknown_email_without_creating_an_account(): void
    {
        $this->artisan('ikasgune:make-admin', ['email' => 'unknown@example.test'])
            ->expectsOutput('No existe una cuenta con ese correo. Regístrala primero.')->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }
}
