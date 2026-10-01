<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();

        return view('admin.index', [
            'classes' => SchoolClass::with('teachers')->withCount('students')->orderBy('name')->get(),
            'users' => User::query()->with('schoolClass')->select(['id', 'name', 'email', 'is_admin', 'is_teacher', 'is_registered', 'phone', 'birth_date', 'school_class_id', 'address', 'admin_notes', 'created_at'])->latest('id')->paginate(15),
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'courses' => Course::query()->with('teacher')->latest('id')->get(),
            'teachers' => User::query()->where('is_teacher', true)->orderBy('name')->get(['id', 'name']),
            'roleChart' => [
                ['label' => __('Administradores'), 'count' => $totalAdmins],
                ['label' => __('Profesores'), 'count' => User::query()->where('is_teacher', true)->where('is_admin', false)->count()],
                ['label' => __('Alumnos'), 'count' => User::query()->where('is_admin', false)->where('is_teacher', false)->count()],
            ],
            'enrollmentChart' => Course::query()->withCount('enrollments')->orderByDesc('enrollments_count')->limit(8)->get(['id', 'title', 'translations']),
        ]);
    }
}
