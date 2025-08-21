<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SearchClassGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\ClassGroup::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:255'],
            'level' => ['sometimes', 'string', 'max:100'],
            'academic_year' => ['sometimes', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', 'in:name,level,academic_year,created_at'],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
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
            'q.max' => 'Le terme de recherche ne peut pas dépasser 255 caractères.',
            'level.max' => 'Le niveau ne peut pas dépasser 100 caractères.',
            'academic_year.max' => 'L\'année scolaire ne peut pas dépasser 20 caractères.',
            'sort_by.in' => 'Le tri doit être parmi: nom, niveau, année scolaire, date de création.',
            'sort_order.in' => 'L\'ordre de tri doit être croissant ou décroissant.',
            'per_page.integer' => 'Le nombre d\'éléments par page doit être un nombre entier.',
            'per_page.min' => 'Le nombre d\'éléments par page doit être au minimum 1.',
            'per_page.max' => 'Le nombre d\'éléments par page ne peut pas dépasser 100.',
        ];
    }
}
