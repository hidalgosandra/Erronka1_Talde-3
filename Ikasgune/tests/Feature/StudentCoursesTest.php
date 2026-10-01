<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StudentCoursesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_must_complete_profile_and_cannot_assign_privileges(): void
    {
        $student = User::factory()->create();
        $schoolClass = SchoolClass::factory()->create();
        $this->actingAs($student)->get(route('courses.index'))->assertRedirect(route('profile.edit'));
        $this->get(route('profile.edit'))->assertOk()->assertSee($schoolClass->name);
        $this->put(route('profile.update'), [])->assertSessionHasErrors(['birth_date', 'school_class_id']);
        $this->put(route('profile.update'), ['birth_date' => now()->addDay()->toDateString(), 'school_class_id' => 99999])
            ->assertSessionHasErrors(['birth_date', 'school_class_id']);
        $this->put(route('profile.update'), [
            'birth_date' => '2006-04-12', 'school_class_id' => $schoolClass->id, 'is_admin' => true, 'is_teacher' => true,
        ])->assertRedirect(route('courses.index'))->assertSessionHasNoErrors();
        $student->refresh();
        $this->assertSame($schoolClass->id, $student->school_class_id);
        $this->assertSame('2006-04-12', $student->birth_date->toDateString());
        $this->assertFalse($student->is_admin);
        $this->assertFalse($student->is_teacher);
    }

    public function test_only_admin_can_create_classes_and_assign_multiple_teachers(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $teachers = User::factory()->count(2)->create(['is_teacher' => true]);
        $student = User::factory()->create();
        $this->actingAs($student)->post(route('admin.classes.store'), ['name' => 'DAW A'])->assertForbidden();
        $this->actingAs($admin)->post(route('admin.classes.store'), ['name' => 'DAW A', 'teacher_ids' => [$student->id]])
            ->assertSessionHasErrors('teacher_ids.0');
        $this->post(route('admin.classes.store'), ['name' => 'DAW A', 'teacher_ids' => $teachers->modelKeys()])
            ->assertRedirect(route('admin.index', ['tab' => 'admin-classes']))->assertSessionHasNoErrors();
        $schoolClass = SchoolClass::sole();
        $this->assertCount(2, $schoolClass->teachers);
        $this->post(route('admin.classes.store'), ['name' => 'DAW A'])->assertSessionHasErrors('name');
        $this->get(route('admin.index', ['tab' => 'admin-classes']))->assertOk()->assertSee('DAW A');
        $this->put(route('admin.classes.update', $schoolClass), ['name' => 'DAW B', 'teacher_ids' => [$teachers[0]->id]])
            ->assertSessionHasNoErrors();
        $this->assertCount(1, $schoolClass->fresh()->teachers);
    }

    public function test_catalog_home_detail_and_enrollment_are_limited_to_class_teachers(): void
    {
        $schoolClass = SchoolClass::factory()->create();
        $teachers = User::factory()->count(2)->create(['is_teacher' => true]);
        $schoolClass->teachers()->attach($teachers->modelKeys());
        $student = User::factory()->inClass($schoolClass)->create();
        $first = Course::factory()->create(['teacher_id' => $teachers[0]->id, 'is_featured' => true]);
        $second = Course::factory()->create(['teacher_id' => $teachers[1]->id, 'is_featured' => true]);
        $outside = Course::factory()->create(['teacher_id' => User::factory()->create(['is_teacher' => true])->id, 'is_featured' => true]);
        $unassigned = Course::factory()->create(['is_featured' => true]);

        foreach (['courses.index', 'inicio'] as $route) {
            $this->actingAs($student)->get(route($route))->assertOk()->assertSee($first->title)->assertSee($second->title)
                ->assertDontSee($outside->title)->assertDontSee($unassigned->title);
        }
        $this->get(route('courses.show', $outside))->assertNotFound();
        $this->get(route('courses.join', $outside))->assertNotFound();
        $this->post(route('enrollments.store', $outside))->assertNotFound();
        $this->post(route('enrollments.store', $first))->assertRedirect(route('courses.show', $first));
        $this->assertDatabaseHas('enrollments', ['user_id' => $student->id, 'course_id' => $first->id]);
        $this->assertDatabaseMissing('enrollments', ['user_id' => $student->id, 'course_id' => $outside->id]);

        $schoolClass->teachers()->detach();
        $this->get(route('courses.show', $first))->assertNotFound();
        $this->get(route('courses.mine'))->assertOk()->assertDontSee($first->title);
        $this->get(route('dashboard'))->assertOk()->assertDontSee($first->title);
    }

    public function test_teacher_cannot_link_students_from_another_class_or_see_them_in_selector(): void
    {
        $teacher = User::factory()->create(['is_teacher' => true]);
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $student = User::factory()->inClass(SchoolClass::factory()->create())->create();
        $this->actingAs($teacher)->get(route('teacher.index'))->assertOk()->assertDontSee($student->email);
        $this->post(route('teacher.students.store', $course), ['user_id' => $student->id])->assertForbidden();
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_teacher_charts_only_include_own_courses_and_count_distinct_students(): void
    {
        $teacher = User::factory()->create(['is_teacher' => true]);
        $own = Course::factory()->create(['teacher_id' => $teacher->id]);
        $other = Course::factory()->create(['teacher_id' => User::factory()->create(['is_teacher' => true])->id]);
        $student = User::factory()->create();
        $own->enrollments()->create(['user_id' => $student->id]);
        $this->actingAs($teacher)->get(route('teacher.index'))->assertOk()
            ->assertSee('Materiales por curso')->assertSee('Inscripciones por curso')
            ->assertSee('aria-valuenow="1"', false)->assertSee($own->title)->assertDontSee($other->title);
    }

    public function test_profile_is_authenticated_and_translated(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $student = User::factory()->create();
        foreach (['en' => 'Academic profile', 'eu' => 'Ikasketa-profila', 'es' => 'Perfil académico'] as $locale => $title) {
            $this->actingAs($student)->withSession(['locale' => $locale])->get(route('profile.edit'))->assertOk()->assertSee($title);
        }
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->put(route('profile.update'), [])->assertForbidden();
    }
}
