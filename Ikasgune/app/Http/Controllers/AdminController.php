<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'users' => User::query()->select(['id', 'name', 'email', 'is_admin', 'is_registered', 'phone', 'birth_date', 'address', 'admin_notes', 'created_at'])->latest('id')->paginate(15),
            'totalUsers' => User::count(),
            'totalAdmins' => User::where('is_admin', true)->count(),
            'courses' => Course::query()->latest('id')->get(),
        ]);
    }
}
