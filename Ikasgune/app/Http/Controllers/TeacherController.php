<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        return view('teacher.index', [
            'courses' => $request->user()->teachingCourses()->with(['enrollments.user', 'materials'])->latest('id')->get(),
            'students' => User::query()->where('is_registered', true)->where('is_admin', false)->where('is_teacher', false)
                ->whereHas('schoolClass.teachers', fn ($teachers) => $teachers->where('users.id', $request->user()->id))
                ->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function enrollStudent(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_registered', 1)->where('is_admin', 0)->where('is_teacher', 0))],
        ]);
        $student = User::findOrFail($data['user_id']);
        abort_unless($student->hasStudentProfile() && Course::query()->visibleTo($student)->whereKey($course->id)->exists(), 403);
        $enrollment = Enrollment::query()->firstOrCreate(['course_id' => $course->id, 'user_id' => $data['user_id']]);

        return back()->with('status', $enrollment->wasRecentlyCreated ? __('El alumno se ha vinculado al curso.') : __('El alumno ya está inscrito en este curso.'));
    }

    public function removeStudent(Course $course, User $user): RedirectResponse
    {
        $course->enrollments()->where('user_id', $user->id)->firstOrFail()->delete();

        return back()->with('status', __('El alumno se ha desvinculado del curso.'));
    }

    public function storeMaterial(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $file = $request->file('file');
        abort_unless($file instanceof UploadedFile, 422);
        $path = $file->store('course-materials/'.$course->id, 'local');

        $course->materials()->create([
            'title' => $data['title'],
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
        ]);

        return back()->with('status', __('El material se ha añadido al curso.'));
    }

    public function removeMaterial(Course $course, CourseMaterial $courseMaterial): RedirectResponse
    {
        abort_unless($courseMaterial->course_id === $course->id, 404);
        Storage::disk('local')->delete($courseMaterial->file_path);
        $courseMaterial->delete();

        return back()->with('status', __('El material se ha eliminado.'));
    }

    public function download(Request $request, CourseMaterial $courseMaterial): BinaryFileResponse
    {
        $user = $request->user();
        $canDownload = $user->can('access-admin')
            || ($user->is_teacher && $courseMaterial->course()->where('teacher_id', $user->id)->exists())
            || ($user->enrollments()->where('course_id', $courseMaterial->course_id)->exists()
                && Course::query()->visibleTo($user)->whereKey($courseMaterial->course_id)->exists());
        abort_unless($canDownload, 403);

        return response()->download(Storage::disk('local')->path($courseMaterial->file_path), $courseMaterial->original_name);
    }
}
