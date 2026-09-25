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
            'translations' => ['sometimes', 'array:eu,en'],
            'translations.*' => ['array:title,description'],
            'translations.*.title' => ['nullable', 'string', 'max:255'],
            'translations.*.description' => ['nullable', 'string', 'max:10000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => __('Introduce el título del curso.'),
            'title.string' => __('Introduce un título válido.'),
            'title.max' => __('El título no puede superar los 255 caracteres.'),
            'description.required' => __('Introduce la descripción del curso.'),
            'description.string' => __('Introduce una descripción válida.'),
            'description.max' => __('La descripción no puede superar los 10000 caracteres.'),
            'category.required' => __('Introduce una categoría.'),
            'level.required' => __('Selecciona un nivel.'),
            'duration_minutes.required' => __('Indica la duración del curso.'),
            'duration_minutes.integer' => __('La duración debe ser un número entero.'),
            'duration_minutes.min' => __('La duración mínima es de 15 minutos.'),
            'duration_minutes.max' => __('La duración máxima es de 1000 minutos.'),
        ];
    }
}
