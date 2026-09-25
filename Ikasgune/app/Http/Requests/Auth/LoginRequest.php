<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required' => __('Introduce tu correo electrónico.'),
            'email.string' => __('Introduce un correo electrónico válido.'),
            'email.email' => __('Introduce un correo electrónico válido.'),
            'email.max' => __('El correo no puede superar los 255 caracteres.'),
            'password.required' => __('Introduce tu contraseña.'),
            'password.string' => __('Introduce una contraseña válida.'),
            'remember.boolean' => __('La opción Recordarme no es válida.'),
        ];
    }

    public function authenticate(): void
    {
        $key = 'login:'.hash('sha256', Str::lower($this->string('email')->toString()).'|'.$this->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Demasiados intentos. Vuelve a intentarlo en '.RateLimiter::availableIn($key).' segundos.',
            ]);
        }

        if (! Auth::attempt($this->safe()->only(['email', 'password']), $this->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => __('El correo o la contraseña no son correctos.'),
            ]);
        }

        RateLimiter::clear($key);
    }
}
