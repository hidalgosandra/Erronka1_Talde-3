<?php

namespace Tests\Feature;

use App\Mail\RegistrationVerificationCode;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class AccountRecoveryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_recovery_pages_are_accessible(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Recuperar contraseña');
        $this->get(route('password.reset', ['token' => 'test', 'email' => 'student@example.test']))->assertOk()->assertSee('Nueva contraseña');
    }

    public function test_reset_link_is_sent_only_for_active_accounts_and_response_does_not_reveal_accounts(): void
    {
        Notification::fake();
        $active = User::factory()->create();
        $pending = User::factory()->create(['is_registered' => false]);
        $this->post(route('password.email'), ['email' => $active->email])->assertSessionHas('status');
        Notification::assertSentTo($active, ResetPassword::class);
        $message = session('status');
        $this->post(route('password.email'), ['email' => $pending->email])->assertSessionHas('status', $message);
        Notification::assertNotSentTo($pending, ResetPassword::class);
        $this->post(route('password.email'), ['email' => 'missing@example.test'])->assertSessionHas('status', $message);
    }

    public function test_valid_reset_changes_password_rotates_remember_token_and_cannot_be_reused(): void
    {
        $user = User::factory()->create();
        $previousRemember = $user->remember_token;
        $token = Password::createToken($user);
        $payload = ['email' => $user->email, 'token' => $token, 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'];

        $this->post(route('password.update'), $payload)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check($payload['password'], $user->fresh()->password));
        $this->assertNotSame($previousRemember, $user->fresh()->remember_token);
        $this->assertGuest();
        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
    }

    public function test_invalid_and_expired_reset_tokens_cannot_change_password(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $original = $user->password;
        $payload = ['email' => $user->email, 'token' => 'invalid', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'];
        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
        $payload['token'] = Password::createToken($user);
        $this->travel(61)->minutes();
        $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
        $this->assertSame($original, $user->fresh()->password);
    }

    public function test_password_confirmation_is_required(): void
    {
        $user = User::factory()->create();
        $this->post(route('password.update'), [
            'email' => $user->email, 'token' => Password::createToken($user),
            'password' => 'new-password-123', 'password_confirmation' => 'not-matching',
        ])->assertSessionHasErrors('password')->assertSessionMissing('_old_input.password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_reset_notification_uses_configured_site_url(): void
    {
        config(['app.url' => 'https://eskolak.example']);
        $user = User::factory()->create();
        $mail = (new ResetPassword('test-token'))->toMail($user);
        $this->assertStringStartsWith('https://eskolak.example/reset-password/test-token', $mail->actionUrl);
        $this->assertSame('Recupera tu contraseña de Eskolak', $mail->subject);
    }

    public function test_resend_preserves_registration_data_replaces_code_and_limits_requests(): void
    {
        Mail::fake();
        $registration = [
            'name' => 'Ane', 'email' => 'ane@example.test', 'password' => Hash::make('long-password-123'),
            'code' => Hash::make('000000'), 'expires_at' => now()->subMinute()->timestamp,
        ];
        $this->withSession(['registration' => $registration])->post(route('register.resend'))
            ->assertRedirect(route('register.verify'))->assertSessionHas('registration.email', 'ane@example.test')
            ->assertSessionHas('registration.password', $registration['password']);
        $sent = Mail::sent(RegistrationVerificationCode::class)->sole();
        $this->assertTrue(Hash::check($sent->code, session('registration.code')));
        $this->assertFalse(Hash::check('000000', session('registration.code')));
        $this->post(route('register.resend'))->assertTooManyRequests();
        $this->post(route('register.verify.store'), ['verification_code' => $sent->code])->assertRedirect(route('dashboard'));
        $this->assertNotNull(User::where('email', 'ane@example.test')->sole()->email_verified_at);
    }

    public function test_resend_without_registration_redirects_to_register(): void
    {
        Mail::fake();
        $this->post(route('register.resend'))->assertRedirect(route('register'));
        Mail::assertNothingSent();
    }

    public function test_failed_resend_keeps_previous_code_valid(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.example.test']);
        Mail::shouldReceive('to')->once()->andThrow(new TransportException('SMTP secret must not be exposed'));
        $registration = ['email' => 'ane@example.test', 'code' => Hash::make('123456'), 'expires_at' => now()->addMinutes(5)->timestamp];
        $this->withSession(['registration' => $registration])->post(route('register.resend'))
            ->assertSessionHasErrors(['verification_code' => 'No se ha podido enviar el correo. Inténtalo de nuevo más tarde.']);
        $this->assertSame($registration['code'], session('registration.code'));
    }

    public function test_unconfigured_mail_shows_an_error_instead_of_claiming_delivery(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.gmail.com', 'mail.mailers.smtp.password' => null]);
        $this->post(route('password.email'), ['email' => 'ane@example.test'])
            ->assertSessionHasErrors(['email' => 'El envío de correo todavía no está configurado. Contacta con el administrador.'])
            ->assertSessionMissing('status');
    }

    public function test_reset_link_requests_are_rate_limited(): void
    {
        Notification::fake();
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.email'), ['email' => 'missing@example.test']);
        }
        $this->post(route('password.email'), ['email' => 'missing@example.test'])->assertTooManyRequests();
    }
}
