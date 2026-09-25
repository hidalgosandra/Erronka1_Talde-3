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
            'name.required' => __('Introduce tu nombre.'),
            'name.string' => __('Introduce un nombre válido.'),
            'name.max' => __('El nombre no puede superar los 255 caracteres.'),
            'email.required' => __('Introduce tu correo electrónico.'),
            'email.string' => __('Introduce un correo electrónico válido.'),
            'email.email' => __('Introduce un correo electrónico válido.'),
            'email.max' => __('El correo no puede superar los 255 caracteres.'),
            'email.unique' => __('Ya existe una cuenta con ese correo.'),
            'password.required' => __('Introduce una contraseña.'),
            'password.string' => __('Introduce una contraseña válida.'),
            'password.min' => __('La contraseña debe tener al menos 12 caracteres.'),
            'password.max' => __('La contraseña no puede superar los 72 caracteres.'),
            'password.confirmed' => __('Las contraseñas no coinciden.'),
        ];
    }
}
