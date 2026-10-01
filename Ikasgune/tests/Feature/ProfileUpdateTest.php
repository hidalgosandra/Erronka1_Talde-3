<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_update_personal_data_and_password(): void
    {
        $user = User::factory()->create(['email' => 'old@example.test', 'is_teacher' => true]);

        $this->actingAs($user)->get(route('account.profile.edit'))->assertOk()->assertSee('Mis datos');
        $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => 'Nombre actualizado', 'email' => 'new@example.test',
            'phone' => '+34 600 000 000', 'address' => 'Calle Eskolak 1',
            'birth_date' => '1995-05-20', 'password' => 'new-password-value',
            'password_confirmation' => 'new-password-value',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertSame('Nombre actualizado', $user->name);
        $this->assertSame('new@example.test', $user->email);
        $this->assertSame('+34 600 000 000', $user->phone);
        $this->assertTrue(Hash::check('new-password-value', $user->password));
    }

    public function test_student_profile_update_saves_academic_class(): void
    {
        $student = User::factory()->create();
        $schoolClass = SchoolClass::factory()->create();

        $this->actingAs($student)->put(route('profile.update'), [
            'name' => $student->name, 'email' => $student->email,
            'birth_date' => '2000-01-01', 'school_class_id' => $schoolClass->id,
        ])->assertRedirect(route('courses.index'));

        $this->assertDatabaseHas('users', ['id' => $student->id, 'school_class_id' => $schoolClass->id]);
    }

    public function test_profile_update_preserves_teacher_role_and_course_assignment(): void
    {
        $teacher = User::factory()->create(['is_teacher' => true]);
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)->put(route('account.profile.update'), [
            'name' => 'Teacher updated', 'email' => $teacher->email,
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue($teacher->fresh()->is_teacher);
        $this->assertSame($teacher->id, $course->fresh()->teacher_id);
    }
}
