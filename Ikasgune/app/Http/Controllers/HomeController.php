<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $hasApplicationTables = Schema::hasTable('courses') && Schema::hasTable('users') && Schema::hasTable('enrollments');

        return view('inicio', [
            'featuredCourses' => $hasApplicationTables ? Course::query()->visibleTo($request->user())->where('is_featured', true)->latest('id')->limit(3)->get() : collect(),
            'courseCount' => $hasApplicationTables ? Course::query()->visibleTo($request->user())->count() : 0,
            'learnerCount' => $hasApplicationTables ? User::count() : 0,
            'enrollmentCount' => $hasApplicationTables ? Enrollment::count() : 0,
        ]);
    }
}
