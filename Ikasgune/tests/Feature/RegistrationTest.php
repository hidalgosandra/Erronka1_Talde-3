<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_can_open_registration(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Crear cuenta');
    }

    public function test_registration_creates_a_normal_user_and_logs_them_in(): void
    {
        $this->withSession(['marker' => 'before']);
        $previousSession = session()->getId();

        $this->post(route('register.store'), [
            'name' => 'Ander', 'email' => 'ander@example.test',
            'password' => 'long-test-password', 'password_confirmation' => 'long-test-password',
            'is_admin' => true, 'email_verified_at' => now()->toDateTimeString(),
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'ander@example.test')->sole();
        $this->assertSame('Ander', $user->name);
        $this->assertFalse($user->is_admin);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('long-test-password', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($previousSession, session()->getId());
    }

    public function test_registration_rejects_duplicate_emails(): void
    {
        $user = User::factory()->create();

        $this->post(route('register.store'), [
            'name' => 'Another', 'email' => $user->email,
            'password' => 'long-test-password', 'password_confirmation' => 'long-test-password',
        ])->assertSessionHasErrors(['email' => 'Ya existe una cuenta con ese correo.']);

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_registration_requires_all_fields(): void
    {
        $this->post(route('register.store'), [])->assertSessionHasErrors([
            'name' => 'Introduce tu nombre.',
            'email' => 'Introduce tu correo electrónico.',
            'password' => 'Introduce una contraseña.',
        ]);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_bad_email_short_password_and_confirmation(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Ander', 'email' => 'invalid',
            'password' => 'short', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['email', 'password'])->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_registration_rejects_mismatched_confirmation(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Ander', 'email' => 'ander@example.test',
            'password' => 'long-test-password', 'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors(['password' => 'Las contraseñas no coinciden.']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_authenticated_users_cannot_register_another_account(): void
    {
        $this->actingAs(User::factory()->create())->post(route('register.store'), [])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_limits_repeated_requests(): void
    {
        $this->freezeTime();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('register.store'), []);
        }

        $this->post(route('register.store'), [])->assertTooManyRequests();
        $this->assertDatabaseCount('users', 0);
    }
}
