<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_can_open_login(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Iniciar sesión');
    }

    public function test_guests_are_redirected_from_the_private_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_valid_credentials_regenerate_session_and_log_in(): void
    {
        $user = User::factory()->create();
        $this->withSession(['marker' => 'before-login']);
        $previousSession = session()->getId();

        $response = $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($previousSession, session()->getId());
    }

    public function test_login_returns_to_the_requested_private_page(): void
    {
        $user = User::factory()->create();

        $this->withSession(['url.intended' => route('dashboard').'?from=menu'])
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard').'?from=menu');
    }

    public function test_incorrect_password_is_rejected_and_never_flashed(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrect-password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.'])
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public function test_missing_fields_have_spanish_validation_errors(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors([
            'email' => 'Introduce tu correo electrónico.',
            'password' => 'Introduce tu contraseña.',
        ]);

        $this->assertGuest();
    }

    public function test_malformed_email_and_remember_value_are_rejected(): void
    {
        $this->post(route('login.store'), ['email' => 'invalid', 'password' => 'password', 'remember' => 'invalid'])
            ->assertSessionHasErrors([
                'email' => 'Introduce un correo electrónico válido.',
                'remember' => 'La opción Recordarme no es válida.',
            ]);

        $this->assertGuest();
    }

    public function test_remember_me_issues_a_persistent_cookie(): void
    {
        $user = User::factory()->create(['remember_token' => null]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password', 'remember' => '1'])
            ->assertRedirect(route('dashboard'))
            ->assertCookie(Auth::guard('web')->getRecallerName());

        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_login_is_temporarily_blocked_after_five_failures(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Demasiados intentos. Vuelve a intentarlo en 60 segundos.']);
        $this->assertGuest();

        $this->travel(61)->seconds();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_private_page_escapes_the_user_name(): void
    {
        $user = User::factory()->create(['name' => '<script>alert(1)</script>']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertSee($user->name)
            ->assertDontSee($user->name, false)
            ->assertSee('Cerrar sesión');
    }

    public function test_authenticated_users_are_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())->get(route('login'))->assertRedirect(route('dashboard'));
    }

    public function test_logout_invalidates_the_session_and_protects_the_private_page(): void
    {
        $this->actingAs(User::factory()->create())->withSession(['private-marker' => 'secret']);
        $previousToken = session()->token();

        $this->post(route('logout'))->assertRedirect(route('login'))->assertSessionMissing('private-marker');

        $this->assertGuest();
        $this->assertNotSame($previousToken, session()->token());
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_logout_does_not_accept_get_requests(): void
    {
        $this->actingAs(User::factory()->create())->get(route('logout'))->assertMethodNotAllowed();
        $this->assertAuthenticated();
    }
}
