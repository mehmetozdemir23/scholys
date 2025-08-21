<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreClassGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ClassGroup::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:100'],
            'section' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:1000'],
            'max_students' => ['nullable', 'integer', 'min:1', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la classe est obligatoire.',
            'name.max' => 'Le nom de la classe ne peut pas dépasser 255 caractères.',
            'level.max' => 'Le niveau ne peut pas dépasser 100 caractères.',
            'section.max' => 'La section ne peut pas dépasser 10 caractères.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
            'max_students.integer' => 'Le nombre maximum d\'élèves doit être un nombre entier.',
            'max_students.min' => 'Le nombre maximum d\'élèves doit être au minimum 1.',
            'max_students.max' => 'Le nombre maximum d\'élèves ne peut pas dépasser 100.',
            'academic_year.required' => 'L\'année scolaire est obligatoire.',
            'academic_year.max' => 'L\'année scolaire ne peut pas dépasser 20 caractères.',
        ];
    }
}
