<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MailDelivery;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']], [
            'email.required' => __('Introduce tu correo electrónico.'),
            'email.email' => __('Introduce un correo electrónico válido.'),
        ]);
        MailDelivery::send(fn () => Password::sendResetLink($data + ['is_registered' => true]));

        return back()->with('status', __('Si existe una cuenta activa con ese correo, recibirás un enlace para restablecer la contraseña.'));
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed'],
        ], [
            'password.min' => __('La contraseña debe tener al menos 12 caracteres.'),
            'password.max' => __('La contraseña no puede superar los 72 caracteres.'),
            'password.confirmed' => __('Las contraseñas no coinciden.'),
            'password.required' => __('Introduce una contraseña.'),
            'email.required' => __('Introduce tu correo electrónico.'),
            'email.email' => __('Introduce un correo electrónico válido.'),
            'token.required' => __('El enlace no es válido. Solicita uno nuevo.'),
        ]);
        $status = Password::reset($data + ['is_registered' => true], function (User $user, string $password): void {
            $user->password = $password;
            $user->setRememberToken(Str::random(60));
            $user->save();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
            event(new PasswordReset($user));
        });

        if ($status !== Password::PasswordReset) {
            return back()->withErrors(['email' => __('El enlace no es válido o ha caducado. Solicita uno nuevo.')])->withInput($request->only('email'));
        }

        return redirect()->route('login')->with('status', __('Contraseña actualizada. Ya puedes iniciar sesión.'));
    }
}
