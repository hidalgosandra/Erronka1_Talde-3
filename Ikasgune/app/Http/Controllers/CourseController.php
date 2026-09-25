<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::query();
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(fn ($builder) => $builder
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('translations->'.app()->getLocale().'->title', 'like', "%{$search}%")
                ->orWhere('translations->'.app()->getLocale().'->description', 'like', "%{$search}%"));
        }

        if ($request->filled('category')) {
            $query->where('category', (string) $request->string('category'));
        }

        if ($request->filled('level')) {
            $query->where('level', (string) $request->string('level'));
        }

        match ($request->query('sort')) {
            'duration' => $query->orderBy('duration_minutes'),
            'popular' => $query->withCount('enrollments')->orderByDesc('enrollments_count'),
            default => $query->latest('id'),
        };

        return view('courses.index', [
            'courses' => $query->paginate(12)->withQueryString(),
            'categories' => Course::query()->distinct()->orderBy('category')->pluck('category'),
            'levels' => Course::query()->distinct()->orderBy('level')->pluck('level'),
        ]);
    }

    public function show(Request $request, Course $course): View
    {
        return view('courses.show', [
            'course' => $course,
            'isEnrolled' => $request->user()?->enrollments()->where('course_id', $course->id)->exists() ?? false,
        ]);
    }
}
