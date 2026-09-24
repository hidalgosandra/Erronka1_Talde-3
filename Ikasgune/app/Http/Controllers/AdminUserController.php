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
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $user->update([
            ...$data,
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return back()->with('status', 'Erabiltzailearen datuak eguneratu dira.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'Ezin duzu zure administratzaile kontua ezabatu.');

        $user->delete();

        return back()->with('status', 'Erabiltzailea ezabatu da.');
    }
}
