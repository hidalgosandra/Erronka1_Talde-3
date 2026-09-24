<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        User::create([
            ...$data,
            'password' => Hash::make(Str::random(48)),
            'is_admin' => $request->boolean('is_admin'),
            'is_registered' => false,
        ]);

        return back()->with('status', 'Ikaslea gehitu da. Orain ikasleak kontu hori aktiba dezake.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'password' => ['nullable', 'string', 'min:12', 'max:72', 'confirmed'],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $update = [
            ...$data,
            'is_admin' => $request->boolean('is_admin'),
        ];

        if (blank($update['password'] ?? null)) {
            unset($update['password']);
        }

        $user->update($update);

        return back()->with('status', 'Erabiltzailearen datuak eguneratu dira.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'Ezin duzu zure administratzaile kontua ezabatu.');

        $user->delete();

        return back()->with('status', 'Erabiltzailea ezabatu da.');
    }
}
