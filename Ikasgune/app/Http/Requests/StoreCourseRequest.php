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
            'category' => ['sometimes', 'string', 'max:80'],
            'level' => ['sometimes', 'string', 'max:80'],
            'duration_minutes' => ['sometimes', 'integer', 'min:15', 'max:1000'],
            'is_featured' => ['sometimes', 'boolean'],
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
            'category.required' => 'Introduce una categoría.',
            'level.required' => 'Selecciona un nivel.',
            'duration_minutes.required' => 'Indica la duración del curso.',
            'duration_minutes.integer' => 'La duración debe ser un número entero.',
            'duration_minutes.min' => 'La duración mínima es de 15 minutos.',
            'duration_minutes.max' => 'La duración máxima es de 1000 minutos.',
        ];
    }
}
