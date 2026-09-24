<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $hasApplicationTables = Schema::hasTable('courses') && Schema::hasTable('users') && Schema::hasTable('enrollments');

        return view('inicio', [
            'featuredCourses' => $hasApplicationTables ? Course::query()->where('is_featured', true)->latest('id')->limit(3)->get() : collect(),
            'courseCount' => $hasApplicationTables ? Course::count() : 0,
            'learnerCount' => $hasApplicationTables ? User::count() : 0,
            'enrollmentCount' => $hasApplicationTables ? Enrollment::count() : 0,
        ]);
    }
}
