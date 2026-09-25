<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\RegistrationVerificationCode;
use App\Models\User;
use App\Support\MailDelivery;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email', 'password']);
        $code = (string) random_int(100000, 999999);

        MailDelivery::send(fn () => Mail::to($data['email'])->send(new RegistrationVerificationCode($code)));
        $request->session()->put('registration', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->route('register.verify')->with('status', __('Te hemos enviado un código de verificación.'));
    }

    public function verify(): View|RedirectResponse
    {
        if (! session()->has('registration')) {
            return redirect()->route('register');
        }

        return view('auth.verify-registration');
    }

    public function resend(Request $request): RedirectResponse
    {
        $registration = $request->session()->get('registration');
        if (! is_array($registration)) {
            return redirect()->route('register');
        }
        $code = (string) random_int(100000, 999999);
        MailDelivery::send(fn () => Mail::to($registration['email'])->send(new RegistrationVerificationCode($code)), 'verification_code');
        $registration['code'] = Hash::make($code);
        $registration['expires_at'] = now()->addMinutes(10)->timestamp;
        $request->session()->put('registration', $registration);

        return redirect()->route('register.verify')->with('status', __('Hemos enviado un nuevo código. El anterior ya no es válido.'));
    }

    public function verifyStore(Request $request): RedirectResponse
    {
        $registration = $request->session()->get('registration');

        if (! is_array($registration)) {
            return redirect()->route('register')->withErrors(['verification_code' => __('Solicita un nuevo código de verificación.')]);
        }

        $request->validate([
            'verification_code' => ['required', 'digits:6'],
        ], [
            'verification_code.required' => __('Introduce el código de verificación.'),
            'verification_code.digits' => __('El código debe tener 6 dígitos.'),
        ]);

        if (($registration['expires_at'] ?? 0) < now()->timestamp) {
            return back()->withErrors(['verification_code' => __('El código ha caducado. Solicita uno nuevo.')]);
        }

        if (! Hash::check((string) $request->string('verification_code'), $registration['code'])) {
            return back()->withErrors(['verification_code' => __('El código no es correcto.')]);
        }

        $user = User::where('email', $registration['email'])->first();
        if ($user && $user->is_registered) {
            $request->session()->forget('registration');

            return redirect()->route('login')->with('status', __('Esta cuenta ya está activa. Inicia sesión o recupera tu contraseña.'));
        }

        if ($user) {
            $user->update([
                'name' => $registration['name'],
                'password' => $registration['password'],
                'is_registered' => true,
            ]);
        } else {
            $user = User::create([
                'name' => $registration['name'],
                'email' => $registration['email'],
                'password' => $registration['password'],
                'is_registered' => true,
            ]);
        }

        $user->email_verified_at = now();
        $user->save();
        $request->session()->forget('registration');
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('status', __('Tu cuenta se ha verificado correctamente.'));
    }
}
