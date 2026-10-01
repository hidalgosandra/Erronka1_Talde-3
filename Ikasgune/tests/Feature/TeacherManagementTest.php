<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_assign_teacher_and_view_admin_charts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Ane Irakasle',
            'email' => 'ane.teacher@example.test',
            'is_teacher' => '1',
        ])->assertRedirect();

        $teacher = User::query()->where('email', 'ane.teacher@example.test')->sole();
        $this->assertTrue($teacher->is_teacher);

        $this->post(route('admin.courses.store'), [
            'title' => 'Diseño de producto',
            'description' => 'Curso de diseño.',
            'teacher_id' => $teacher->id,
        ])->assertRedirect();

        $course = Course::query()->sole();
        $this->assertSame($teacher->id, $course->teacher_id);

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Distribución por rol')
            ->assertSee('Inscripciones por curso')
            ->assertSee($course->title);
    }

    public function test_assigned_teacher_can_link_and_unlink_registered_students(): void
    {
        $teacher = User::factory()->create(['is_teacher' => true]);
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $enrolledStudent = User::factory()->create(['name' => 'Alumno inscrito', 'is_registered' => true]);
        $availableStudent = User::factory()->create(['name' => 'Alumno disponible', 'is_registered' => true]);
        $schoolClass = SchoolClass::factory()->create();
        $schoolClass->teachers()->attach($teacher);
        $availableStudent->update(['school_class_id' => $schoolClass->id, 'birth_date' => '2005-01-01']);
        $enrolledStudent->update(['school_class_id' => $schoolClass->id, 'birth_date' => '2005-01-01']);
        $enrolledStudent->enrollments()->create(['course_id' => $course->id]);

        $this->actingAs($teacher)->get(route('teacher.index'))
            ->assertOk()
            ->assertSee($course->title)
            ->assertSee($enrolledStudent->name)
            ->assertSee($availableStudent->name);

        $this->from(route('teacher.index'))->post(route('teacher.students.store', $course), [
            'user_id' => $availableStudent->id,
        ])->assertRedirect(route('teacher.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $availableStudent->id]);

        $this->from(route('teacher.index'))->delete(route('teacher.students.destroy', [$course, $availableStudent]))
            ->assertRedirect(route('teacher.index'));
        $this->assertDatabaseMissing('enrollments', ['course_id' => $course->id, 'user_id' => $availableStudent->id]);
    }

    public function test_teacher_cannot_manage_another_teachers_course(): void
    {
        $teacher = User::factory()->create(['is_teacher' => true]);
        $otherTeacher = User::factory()->create(['is_teacher' => true]);
        $course = Course::factory()->create(['teacher_id' => $otherTeacher->id]);
        $student = User::factory()->create(['is_registered' => true]);

        $this->actingAs($teacher)->post(route('teacher.students.store', $course), ['user_id' => $student->id])->assertForbidden();
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_course_material_is_only_downloadable_by_enrolled_students_or_staff(): void
    {
        Storage::fake('local');
        $teacher = User::factory()->create(['is_teacher' => true]);
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $student = User::factory()->create(['is_registered' => true]);
        $outsider = User::factory()->create(['is_registered' => true]);
        $schoolClass = SchoolClass::factory()->create();
        $schoolClass->teachers()->attach($teacher);
        $student->update(['school_class_id' => $schoolClass->id, 'birth_date' => '2005-01-01']);
        $outsider->update(['school_class_id' => $schoolClass->id, 'birth_date' => '2005-01-01']);

        $this->actingAs($teacher)->post(route('teacher.materials.store', $course), [
            'title' => 'Guía inicial',
            'file' => UploadedFile::fake()->create('guia.pdf', 40, 'application/pdf'),
        ])->assertRedirect();

        $material = CourseMaterial::query()->sole();
        $this->assertTrue(Storage::disk('local')->exists($material->file_path));

        $this->actingAs($outsider)->get(route('materials.download', $material))->assertForbidden();
        $this->actingAs($teacher)->get(route('materials.download', $material))->assertOk();

        $student->enrollments()->create(['course_id' => $course->id]);
        $this->actingAs($student)->get(route('courses.show', $course))->assertOk()->assertSee('Guía inicial');
        $this->get(route('materials.download', $material))->assertOk();
        $schoolClass->teachers()->detach($teacher);
        $this->get(route('materials.download', $material))->assertForbidden();
    }
}
