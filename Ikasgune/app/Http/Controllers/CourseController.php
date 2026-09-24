<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(): View
    {
        return view('courses.index', ['courses' => Course::latest('id')->paginate(12)]);
    }

    public function show(Request $request, Course $course): View
    {
        return view('courses.show', [
            'course' => $course,
            'isEnrolled' => $request->user()?->enrollments()->where('course_id', $course->id)->exists() ?? false,
        ]);
    }
}
