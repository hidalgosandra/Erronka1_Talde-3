<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->where(fn ($query) => $query->where('is_registered', true))],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Introduce tu nombre.',
            'name.string' => 'Introduce un nombre válido.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'email.required' => 'Introduce tu correo electrónico.',
            'email.string' => 'Introduce un correo electrónico válido.',
            'email.email' => 'Introduce un correo electrónico válido.',
            'email.max' => 'El correo no puede superar los 255 caracteres.',
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'password.required' => 'Introduce una contraseña.',
            'password.string' => 'Introduce una contraseña válida.',
            'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'password.max' => 'La contraseña no puede superar los 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
