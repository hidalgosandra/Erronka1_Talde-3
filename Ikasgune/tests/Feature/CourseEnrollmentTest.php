<?php

namespace Tests\Feature;

use App\Mail\RegistrationVerificationCode;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CourseEnrollmentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_can_browse_courses_and_see_login_action(): void
    {
        $course = Course::factory()->create(['title' => '<script>alert(1)</script>']);
        $this->get(route('courses.index'))->assertOk()->assertSee($course->title)->assertDontSee($course->title, false);
        $this->get(route('courses.show', $course))->assertOk()->assertSee('Acceder para inscribirme')->assertSee($course->title);
    }

    public function test_guest_cannot_enroll_even_by_posting_directly(): void
    {
        $course = Course::factory()->create();
        $this->post(route('enrollments.store', $course))->assertRedirect(route('login'));
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_login_from_a_course_returns_to_confirmation_without_automatic_enrollment(): void
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();
        $this->get(route('courses.join', $course))->assertRedirect(route('login'));
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('courses.join', $course));
        $this->get(route('courses.join', $course))->assertRedirect(route('courses.show', $course));
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_registration_from_a_course_returns_to_the_course(): void
    {
        Mail::fake();
        $course = Course::factory()->create();
        $this->get(route('courses.join', $course))->assertRedirect(route('login'));
        $this->post(route('register.store'), [
            'name' => 'Student', 'email' => 'student@example.test',
            'password' => 'a-long-password', 'password_confirmation' => 'a-long-password',
        ])->assertRedirect(route('register.verify'));
        $code = null;
        Mail::assertSent(RegistrationVerificationCode::class, function (RegistrationVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $this->post(route('register.verify.store'), ['verification_code' => $code])
            ->assertRedirect(route('courses.join', $course));
        $this->assertAuthenticated();
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_enrollment_uses_logged_in_user_and_route_course_and_ignores_duplicates(): void
    {
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user)->post(route('enrollments.store', $course), [
            'user_id' => $otherUser->id, 'course_id' => $otherCourse->id,
        ])->assertRedirect(route('courses.show', $course))
            ->assertSessionHas('status', 'Te has inscrito correctamente en el curso.');
        $this->post(route('enrollments.store', $course))
            ->assertSessionHas('status', 'Ya estás inscrito en este curso.');
        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'course_id' => $course->id]);
        $this->get(route('courses.show', $course))->assertSee('Ya estás inscrito en este curso.');
    }

    public function test_my_courses_only_lists_current_users_enrollments(): void
    {
        $own = Enrollment::factory()->create();
        $other = Enrollment::factory()->create();
        $this->actingAs($own->user)->get(route('courses.mine'))
            ->assertOk()->assertSee($own->course->title)->assertDontSee($other->course->title);
    }

    public function test_guests_cannot_access_my_courses(): void
    {
        $this->get(route('courses.mine'))->assertRedirect(route('login'));
    }

    public function test_unknown_course_cannot_be_enrolled(): void
    {
        $this->actingAs(User::factory()->create())->post(route('enrollments.store', 99999))->assertNotFound();
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_admin_can_publish_a_course(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.courses.store'), ['title' => 'Laravel', 'description' => 'Aprende Laravel.'])
            ->assertRedirect(route('courses.show', Course::sole()));
        $this->assertDatabaseHas('courses', ['title' => 'Laravel', 'description' => 'Aprende Laravel.']);
    }

    public function test_normal_users_cannot_publish_courses(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.courses.store'), [
            'title' => 'Unauthorized', 'description' => 'Unauthorized',
        ])->assertForbidden();
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_admin_course_form_validates_required_fields(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.courses.store'), [])->assertSessionHasErrors([
                'title' => 'Introduce el título del curso.',
                'description' => 'Introduce la descripción del curso.',
            ]);
        $this->assertDatabaseCount('courses', 0);
    }
}
