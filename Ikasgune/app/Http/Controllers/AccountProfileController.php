<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account-profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today', 'after_or_equal:'.now()->subYears(120)->toDateString()],
            'password' => ['nullable', 'string', 'min:12', 'max:72', 'confirmed'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::transaction(function () use ($request, $data): void {
            if (isset($data['password'])) {
                $request->user()->setRememberToken(Str::random(60));
            }
            $request->user()->forceFill($data)->save();

            if (isset($data['password']) && config('session.driver') === 'database') {
                DB::connection(config('session.connection'))
                    ->table(config('session.table', 'sessions'))
                    ->where('user_id', $request->user()->id)
                    ->delete();
            }
        });

        return redirect()->route('dashboard')->with('status', __('Tus datos se han actualizado.'));
    }
}
