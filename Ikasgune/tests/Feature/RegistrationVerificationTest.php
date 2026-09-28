<?php

namespace Tests\Feature;

use App\Mail\RegistrationVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class RegistrationVerificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_sends_six_digits_and_stores_only_hashes(): void
    {
        Mail::fake();
        $this->freezeTime();

        $this->post(route('register.store'), $this->registrationData())
            ->assertRedirect(route('register.verify'))
            ->assertSessionHas('registration.attempts', 0);

        $mail = Mail::sent(RegistrationVerificationCode::class)->sole();
        $this->assertTrue($mail->hasTo('student@gmail.com'));
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $mail->code);
        $this->assertNotSame($mail->code, session('registration.code'));
        $this->assertTrue(Hash::check($mail->code, session('registration.code')));
        $this->assertTrue(Hash::check('long-password-123', session('registration.password')));
        $this->assertSame(now()->addMinutes(10)->timestamp, session('registration.expires_at'));
        $this->get(route('register.verify'))->assertOk()->assertDontSee($mail->code);
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    #[TestWith([0])]
    #[TestWith([-1])]
    public function test_expired_code_cannot_activate_account(int $seconds): void
    {
        $this->freezeTime();
        $pending = $this->pendingRegistration();
        $pending['expires_at'] = now()->addSeconds($seconds)->timestamp;

        $this->withSession(['registration' => $pending])
            ->post(route('register.verify.store'), ['verification_code' => '123456'])
            ->assertSessionHasErrors(['verification_code' => 'El código ha caducado. Solicita uno nuevo.']);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_five_wrong_codes_require_a_new_code_even_when_the_correct_code_is_submitted(): void
    {
        $this->withSession(['registration' => $this->pendingRegistration()]);
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('register.verify.store'), ['verification_code' => '000000'])
                ->assertSessionHasErrors('verification_code')
                ->assertSessionHas('registration.attempts', $attempt);
        }

        $this->post(route('register.verify.store'), ['verification_code' => '123456'])
            ->assertSessionHasErrors(['verification_code' => 'Has agotado los intentos. Solicita un nuevo código.']);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_resending_unlocks_an_exhausted_registration_and_activation_consumes_it(): void
    {
        Mail::fake();
        $pending = $this->pendingRegistration();
        $pending['attempts'] = 5;
        $pending['code'] = Hash::make('000000');

        $this->withSession(['registration' => $pending])->post(route('register.resend'))
            ->assertRedirect(route('register.verify'))
            ->assertSessionHas('registration.attempts', 0);
        $sent = Mail::sent(RegistrationVerificationCode::class)->sole();
        $this->post(route('register.verify.store'), ['verification_code' => '000000'])
            ->assertSessionHasErrors('verification_code');
        $this->post(route('register.verify.store'), ['verification_code' => $sent->code])
            ->assertRedirect(route('dashboard'))->assertSessionMissing('registration');

        $user = User::where('email', 'student@gmail.com')->sole();
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse($user->is_admin);
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'));
        $this->post(route('register.verify.store'), ['verification_code' => $sent->code])
            ->assertRedirect(route('register'));
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_code_without_its_registration_session_cannot_create_a_user(): void
    {
        $this->post(route('register.verify.store'), ['verification_code' => '123456'])
            ->assertRedirect(route('register'));
        $this->get(route('register.verify'))->assertRedirect(route('register'));

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_invalid_code_is_not_flashed_into_session_input(): void
    {
        $this->withSession(['registration' => $this->pendingRegistration()])
            ->post(route('register.verify.store'), ['verification_code' => '12345'])
            ->assertSessionHasErrors(['verification_code' => 'El código debe tener 6 dígitos.'])
            ->assertSessionMissing('_old_input.verification_code');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_missing_gmail_credentials_does_not_claim_code_delivery(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.gmail.com', 'mail.mailers.smtp.password' => null]);

        $this->post(route('register.store'), $this->registrationData())
            ->assertSessionHasErrors(['email' => 'El envío de correo todavía no está configurado. Contacta con el administrador.'])
            ->assertSessionMissing('registration')
            ->assertSessionMissing('status');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_smtp_failure_does_not_store_registration_or_expose_transport_details(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.example.test']);
        Mail::shouldReceive('to')->once()->andThrow(new TransportException('private SMTP diagnostics'));

        $this->post(route('register.store'), $this->registrationData())
            ->assertSessionHasErrors(['email' => 'No se ha podido enviar el correo. Inténtalo de nuevo más tarde.'])
            ->assertSessionMissing('registration')
            ->assertSessionMissing('status');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    #[TestWith(['es', 'Tu código de verificación de Eskolak', 'Verifica tu cuenta de Eskolak'])]
    #[TestWith(['eu', 'Zure Eskolak egiaztapen-kodea', 'Egiaztatu zure Eskolak kontua'])]
    #[TestWith(['en', 'Your Eskolak verification code', 'Verify your Eskolak account'])]
    public function test_verification_email_has_localized_subject_and_html_and_text_versions(string $locale, string $subject, string $heading): void
    {
        app()->setLocale($locale);
        $mail = new RegistrationVerificationCode('123456');

        $mail->assertHasSubject($subject);
        $mail->assertSeeInHtml($heading);
        $mail->assertSeeInHtml('123456');
        $mail->assertSeeInText($heading);
        $mail->assertSeeInText('123456');
    }

    /** @return array<string, string> */
    private function registrationData(): array
    {
        return [
            'name' => 'Student',
            'email' => 'student@gmail.com',
            'password' => 'long-password-123',
            'password_confirmation' => 'long-password-123',
        ];
    }

    /** @return array<string, int|string> */
    private function pendingRegistration(): array
    {
        return [
            'name' => 'Student',
            'email' => 'student@gmail.com',
            'password' => Hash::make('long-password-123'),
            'code' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ];
    }
}
