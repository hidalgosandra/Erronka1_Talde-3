<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('inicio', [
            'featuredCourses' => Course::query()->where('is_featured', true)->latest('id')->limit(3)->get(),
            'courseCount' => Course::count(),
            'learnerCount' => User::count(),
            'enrollmentCount' => Enrollment::count(),
        ]);
    }
}
