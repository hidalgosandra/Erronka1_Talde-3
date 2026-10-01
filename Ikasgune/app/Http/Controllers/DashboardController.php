<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('dashboard', [
            'enrollments' => $request->user()->enrollments()->whereHas('course', fn ($courses) => $courses->visibleTo($request->user()))->with('course')->latest('id')->get(),
        ]);
    }
}
