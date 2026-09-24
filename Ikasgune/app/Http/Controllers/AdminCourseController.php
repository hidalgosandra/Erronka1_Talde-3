<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;

class AdminCourseController extends Controller
{
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::create($request->validated());

        return redirect()->route('courses.show', $course)->with('status', 'Curso publicado. Ya admite inscripciones.');
    }
}
