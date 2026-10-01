<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const STUDENT_EMAIL = 'student@example.test';

    public function test_google_login_requires_oauth_configuration(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get(route('login.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'El acceso con Google todavía no está configurado.');
    }

    public function test_google_login_creates_a_verified_account_and_starts_a_session(): void
    {
        $this->configureGoogle();
        $this->fakeGoogleProfile(self::STUDENT_EMAIL);

        $this->get(route('login.google.redirect'))->assertRedirect();
        $state = session('google_oauth_state');

        $this->get(route('login.google.callback', ['state' => $state, 'code' => 'google-code']))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => self::STUDENT_EMAIL,
            'name' => 'Google Student',
            'is_registered' => true,
        ]);
        $this->assertNotNull(User::query()->where('email', self::STUDENT_EMAIL)->value('email_verified_at'));
    }

    public function test_google_login_activates_a_preregistered_teacher_without_removing_the_role(): void
    {
        $this->configureGoogle();
        $this->fakeGoogleProfile('teacher@example.test');
        $teacher = User::factory()->create([
            'email' => 'teacher@example.test',
            'is_registered' => false,
            'is_teacher' => true,
        ]);

        $this->get(route('login.google.redirect'));
        $this->get(route('login.google.callback', [
            'state' => session('google_oauth_state'),
            'code' => 'google-code',
        ]))->assertRedirect(route('teacher.index'));

        $this->assertAuthenticatedAs($teacher);
        $this->assertTrue((bool) $teacher->fresh()->is_registered);
        $this->assertTrue($teacher->fresh()->is_teacher);
    }

    public function test_google_callback_rejects_an_invalid_state(): void
    {
        $this->configureGoogle();

        $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get(route('login.google.callback', ['state' => 'wrong-state', 'code' => 'google-code']))
            ->assertStatus(419);

        $this->assertGuest();
    }

    public function test_google_callback_rejects_an_unverified_email(): void
    {
        $this->configureGoogle();
        $this->fakeGoogleProfile('unverified@example.test', false);
        $this->get(route('login.google.redirect'));

        $this->get(route('login.google.callback', [
            'state' => session('google_oauth_state'),
            'code' => 'google-code',
        ]))->assertForbidden();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'unverified@example.test']);
    }

    private function configureGoogle(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => route('login.google.callback'),
        ]);
    }

    private function fakeGoogleProfile(string $email, bool $verified = true): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'email' => $email,
                'email_verified' => $verified,
                'name' => 'Google Student',
            ]),
        ]);

    }
}
