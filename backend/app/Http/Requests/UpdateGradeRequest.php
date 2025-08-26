<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateGradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [
            $this->route('grade'),
            $this->route('classGroup'),
            $this->route('student'),
            $this->route('subject'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => ['sometimes', 'required', 'decimal:0,2'],
            'max_value' => ['sometimes', 'required', 'decimal:0,2', 'gte:value'],
            'coefficient' => ['sometimes', 'required', 'decimal:0,2'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:1024'],
            'academic_year' => ['sometimes', 'required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ];
    }
}
