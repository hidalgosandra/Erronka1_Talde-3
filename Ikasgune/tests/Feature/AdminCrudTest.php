<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Mail\RegistrationVerificationCode;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_only_admins_can_manage_courses_and_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.users.store'), [
            'name' => 'Ikaslea',
            'email' => 'ikaslea@example.test',
        ])->assertForbidden();

        $this->actingAs($user)->put(route('admin.courses.update', Course::factory()->create()), [
            'title' => 'Aldaketa',
            'description' => 'Deskribapena',
        ])->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_a_course(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Zibersegurtasuna',
            'description' => 'Oinarrizko ikastaroa.',
        ])->assertRedirect();

        $course = Course::query()->sole();
        $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => 'Zibersegurtasun aurreratua',
            'description' => 'Eduki eguneratua.',
            'category' => 'Sareak',
            'level' => 'Aurreratua',
            'duration_minutes' => 90,
        ])->assertRedirect();
        $this->assertSame('Zibersegurtasun aurreratua', $course->fresh()->title);

        $this->actingAs($admin)->delete(route('admin.courses.destroy', $course))->assertRedirect();
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_admin_pre_registers_student_and_student_can_activate_account(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Ane',
            'email' => 'ane@example.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'ane@example.test', 'is_registered' => false]);

        $this->actingAs($admin)->post(route('logout'));
        $this->post(route('register.store'), [
            'name' => 'Ane Aranburu',
            'email' => 'ane@example.test',
            'password' => 'long-test-password',
            'password_confirmation' => 'long-test-password',
        ])->assertRedirect(route('register.verify'));

        $code = null;
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $this->post(route('register.verify.store'), ['verification_code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'ane@example.test', 'name' => 'Ane Aranburu', 'is_registered' => true]);
    }
}
