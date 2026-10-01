<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::query()->visibleTo($request->user());
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
            'categories' => Course::query()->visibleTo($request->user())->distinct()->orderBy('category')->pluck('category'),
            'levels' => Course::query()->visibleTo($request->user())->distinct()->orderBy('level')->pluck('level'),
        ]);
    }

    public function show(Request $request, Course $course): View
    {
        $user = $request->user();
        abort_unless(Course::query()->visibleTo($user)->whereKey($course->id)->exists(), 404);
        $isEnrolled = $user?->enrollments()->where('course_id', $course->id)->exists() ?? false;
        $canViewMaterials = $user !== null && ($user->can('access-admin') || $isEnrolled || ($user->is_teacher && $course->teacher_id === $user->id));

        return view('courses.show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'materials' => $canViewMaterials ? $course->materials()->latest('id')->get() : collect(),
        ]);
    }
}
