<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Introduce el título del curso.',
            'title.string' => 'Introduce un título válido.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'description.required' => 'Introduce la descripción del curso.',
            'description.string' => 'Introduce una descripción válida.',
            'description.max' => 'La descripción no puede superar los 10000 caracteres.',
        ];
    }
}
