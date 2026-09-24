<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\RegistrationVerificationCode;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        $request->session()->put('registration', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);
        Mail::to($data['email'])->send(new RegistrationVerificationCode($code));

        return redirect()->route('register.verify')->with('status', 'Te hemos enviado un código de verificación.');
    }

    public function verify(): View|RedirectResponse
    {
        if (! session()->has('registration')) {
            return redirect()->route('register');
        }

        return view('auth.verify-registration');
    }

    public function verifyStore(Request $request): RedirectResponse
    {
        $registration = $request->session()->get('registration');

        if (! is_array($registration)) {
            return redirect()->route('register')->withErrors(['verification_code' => 'Solicita un nuevo código de verificación.']);
        }

        $request->validate([
            'verification_code' => ['required', 'digits:6'],
        ], [
            'verification_code.required' => 'Introduce el código de verificación.',
            'verification_code.digits' => 'El código debe tener 6 dígitos.',
        ]);

        if (($registration['expires_at'] ?? 0) < now()->timestamp) {
            return back()->withErrors(['verification_code' => 'El código ha caducado. Solicita uno nuevo.']);
        }

        if (! Hash::check((string) $request->string('verification_code'), $registration['code'])) {
            return back()->withErrors(['verification_code' => 'El código no es correcto.']);
        }

        $user = User::where('email', $registration['email'])->where('is_registered', false)->first();

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

        $request->session()->forget('registration');
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('status', 'Tu cuenta se ha verificado correctamente.');
    }
}
