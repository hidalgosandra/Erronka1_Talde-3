<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new SchoolClass);
    }

    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        return $this->save($request, $schoolClass);
    }

    private function save(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('school_classes')->ignore($schoolClass)],
            'teacher_ids' => ['sometimes', 'array', 'max:50'],
            'teacher_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where('is_teacher', true)],
        ]);

        DB::transaction(function () use ($schoolClass, $data): void {
            $schoolClass->fill(['name' => $data['name']])->save();
            $schoolClass->teachers()->sync($data['teacher_ids'] ?? []);
        });

        return redirect()->route('admin.index', ['tab' => 'admin-classes'])->with('status', __('Clase guardada.'));
    }
}
