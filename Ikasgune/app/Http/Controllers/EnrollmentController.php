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
            'enrollments' => $request->user()->enrollments()->with('course')->latest('id')->paginate(12),
        ]);
    }

    public function create(Course $course): RedirectResponse
    {
        return redirect()->route('courses.show', $course);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $enrollment = $request->user()->enrollments()->firstOrCreate(['course_id' => $course->id]);

        return redirect()->route('courses.show', $course)->with('status',
            $enrollment->wasRecentlyCreated ? 'Te has inscrito correctamente en el curso.' : 'Ya estás inscrito en este curso.');
    }
}
