<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->isStudent(), 403);

        return view('profile', ['classes' => SchoolClass::orderBy('name')->get(), 'user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);
        $data = $request->validate([
            'birth_date' => ['required', 'date_format:Y-m-d', 'before:today', 'after_or_equal:'.now()->subYears(120)->toDateString()],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
        ]);
        $request->user()->forceFill($data)->save();

        return redirect()->route('courses.index')->with('status', __('Perfil académico guardado.'));
    }
}
