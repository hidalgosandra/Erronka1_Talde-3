<?php

namespace Tests\Feature;

use App\Models\User;
use App\Mail\RegistrationVerificationCode;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_can_open_registration(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Crear cuenta');
    }

    public function test_registration_requires_email_verification_before_creating_a_user(): void
    {
        Mail::fake();

        $this->post(route('register.store'), [
            'name' => 'Ander', 'email' => 'ander@example.test',
            'password' => 'long-test-password', 'password_confirmation' => 'long-test-password',
            'is_admin' => true, 'email_verified_at' => now()->toDateTimeString(),
        ])->assertRedirect(route('register.verify'));

        Mail::assertSent(RegistrationVerificationCode::class, fn (RegistrationVerificationCode $mail): bool => $mail->hasTo('ander@example.test'));
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_correct_verification_code_creates_and_logs_in_the_user(): void
    {
        Mail::fake();

        $this->post(route('register.store'), [
            'name' => 'Ander', 'email' => 'ander@example.test',
            'password' => 'long-test-password', 'password_confirmation' => 'long-test-password',
        ]);
        $code = null;
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $this->post(route('register.verify.store'), ['verification_code' => $code])
            ->assertRedirect(route('dashboard'));

        $user = User::where('email', 'ander@example.test')->sole();
        $this->assertTrue(Hash::check('long-test-password', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_incorrect_verification_code_does_not_create_the_user(): void
    {
        Mail::fake();
        $this->post(route('register.store'), [
            'name' => 'Ander', 'email' => 'ander@example.test',
            'password' => 'long-test-password', 'password_confirmation' => 'long-test-password',
        ]);

        $this->post(route('register.verify.store'), ['verification_code' => '000000'])
            ->assertSessionHasErrors(['verification_code' => 'El código no es correcto.']);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
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
