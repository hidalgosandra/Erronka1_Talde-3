<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        return view('courses.mine', [
            'enrollments' => $request->user()->enrollments()->whereHas('course', fn ($courses) => $courses->visibleTo($request->user()))->with('course')->latest('id')->paginate(12),
        ]);
    }

    public function create(Request $request, Course $course): RedirectResponse
    {
        abort_unless(Course::query()->visibleTo($request->user())->whereKey($course->id)->exists(), 404);

        return redirect()->route('courses.show', $course);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless(Course::query()->visibleTo($request->user())->whereKey($course->id)->exists(), 404);
        $enrollment = $request->user()->enrollments()->firstOrCreate(['course_id' => $course->id]);

        return redirect()->route('courses.show', $course)->with('status',
            $enrollment->wasRecentlyCreated ? __('Te has inscrito correctamente en el curso.') : __('Ya estás inscrito en este curso.'));
    }
}
