<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_pending_accounts_cannot_log_in_even_with_the_correct_password(bool $admin): void
    {
        $user = User::factory()->create(['is_registered' => false, 'is_admin' => $admin, 'email_verified_at' => null]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);
        $this->assertGuest();
    }

    #[TestWith(['POST', 'admin.users.store'])]
    #[TestWith(['PUT', 'admin.users.update'])]
    #[TestWith(['DELETE', 'admin.users.destroy'])]
    public function test_students_cannot_create_edit_or_delete_accounts(string $method, string $route): void
    {
        $student = User::factory()->create();
        $target = User::factory()->create(['name' => 'Protected account', 'is_admin' => true]);

        $this->actingAs($student)->call($method, route($route, ['user' => $target->id]), [
            'name' => 'Attacker', 'email' => 'attacker@example.test', 'is_admin' => true,
        ])->assertForbidden();

        $this->assertDatabaseCount('users', 2);
        $this->assertSame('Protected account', $target->fresh()->name);
        $this->assertTrue($target->fresh()->is_admin);
        $this->assertFalse($student->fresh()->is_admin);
    }

    #[TestWith(['PUT'])]
    #[TestWith(['DELETE'])]
    public function test_students_cannot_modify_other_courses(string $method): void
    {
        $course = Course::factory()->create(['title' => 'Protected course']);
        $this->actingAs(User::factory()->create())
            ->call($method, '/admin/cursos/'.$course->id, ['title' => 'Attacker', 'description' => 'Changed'])
            ->assertForbidden();
        $this->assertSame('Protected course', $course->fresh()->title);
    }

    #[TestWith(['https://evil.example.test'])]
    #[TestWith(['//evil.example.test'])]
    #[TestWith(['/\\evil.example.test'])]
    #[TestWith(["/\tevil.example.test"])]
    public function test_language_switch_cannot_redirect_to_an_external_site(string $destination): void
    {
        $this->post(route('locale.update'), ['locale' => 'en', 'return_to' => $destination])
            ->assertRedirect('/')->assertSessionHas('locale', 'en');
    }

    public function test_locale_cannot_be_used_for_path_traversal(): void
    {
        $this->post(route('locale.update'), ['locale' => '../../.env'])
            ->assertSessionHasErrors('locale')->assertSessionMissing('locale');
    }

    public function test_sql_injection_in_course_filters_does_not_expose_or_delete_records(): void
    {
        $course = Course::factory()->create(['title' => 'Private needle for audit']);
        $payload = "' OR 1=1; DROP TABLE courses; --";

        $this->get(route('courses.index', ['q' => $payload, 'category' => $payload, 'level' => $payload, 'sort' => $payload]))
            ->assertOk()->assertDontSee('Private needle for audit');

        $this->assertModelExists($course);
        $this->assertDatabaseCount('courses', 1);
    }

    public function test_translated_course_content_is_escaped_against_stored_xss(): void
    {
        $payload = '<img src=x onerror=alert(document.cookie)>';
        $course = Course::factory()->create(['translations' => ['en' => ['title' => $payload, 'description' => $payload]]]);

        $this->withSession(['locale' => 'en'])->get(route('courses.show', $course))
            ->assertOk()->assertSee($payload)->assertDontSee($payload, false);
    }

    #[TestWith(['/register'])]
    #[TestWith(['/login'])]
    #[TestWith(['/register/verificar'])]
    #[TestWith(['/register/reenviar'])]
    #[TestWith(['/forgot-password'])]
    #[TestWith(['/reset-password'])]
    #[TestWith(['/idioma'])]
    public function test_sensitive_guest_posts_require_csrf_tokens(string $uri): void
    {
        Mail::fake();
        $this->enforceCsrf();

        $this->post($uri, ['locale' => 'en', 'email' => 'student@example.test'])
            ->assertStatus(419);

        Mail::assertNothingSent();
        $this->assertGuest();
    }

    public function test_admin_write_requires_csrf_even_with_valid_admin_session(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->enforceCsrf();

        $this->actingAs($admin)->post(route('admin.courses.store'), ['title' => 'Injected', 'description' => 'Cross-site'])
            ->assertStatus(419);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_valid_csrf_token_allows_language_change(): void
    {
        $this->enforceCsrf();

        $this->withSession(['_token' => 'test-session-token'])->post(route('locale.update'), [
            '_token' => 'test-session-token', 'locale' => 'eu', 'return_to' => '/cursos',
        ])->assertRedirect('/cursos')->assertSessionHas('locale', 'eu');
    }

    public function test_admin_password_change_revokes_existing_sessions_and_remember_token(): void
    {
        config(['session.driver' => 'database']);
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create(['remember_token' => 'old-remember-token']);
        DB::table('sessions')->insert([
            'id' => 'audit-existing-student-session',
            'user_id' => $student->id,
            'payload' => base64_encode('{}'),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $student), [
            'name' => $student->name, 'email' => $student->email,
            'password' => 'new-safe-password-123', 'password_confirmation' => 'new-safe-password-123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'audit-existing-student-session']);
        $this->assertNotSame('old-remember-token', $student->fresh()->remember_token);
    }

    public function test_editing_profile_without_changing_password_preserves_sessions(): void
    {
        config(['session.driver' => 'database']);
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create(['remember_token' => 'existing-remember-token']);
        DB::table('sessions')->insert([
            'id' => 'audit-preserved-session',
            'user_id' => $student->id,
            'payload' => base64_encode('{}'),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $student), [
            'name' => 'Updated name', 'email' => $student->email, 'password' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sessions', ['id' => 'audit-preserved-session', 'user_id' => $student->id]);
        $this->assertSame('existing-remember-token', $student->fresh()->remember_token);
    }

    private function enforceCsrf(): void
    {
        $this->app->bind(PreventRequestForgery::class, fn ($app) => new class($app, $app['encrypter']) extends PreventRequestForgery
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        });
    }
}
