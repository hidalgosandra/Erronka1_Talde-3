<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminCourseController extends Controller
{
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::create($request->validated() + [
            'category' => 'General',
            'level' => 'Todos los niveles',
            'duration_minutes' => 60,
            'is_featured' => false,
        ]);

        return redirect()->route('courses.show', $course)->with('status', 'Curso publicado. Ya admite inscripciones.');
    }

    public function update(StoreCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update([...$request->validated(), 'is_featured' => $request->boolean('is_featured')]);

        return back()->with('status', 'Ikastaroa eguneratu da.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return back()->with('status', 'Ikastaroa ezabatu da.');
    }
}
