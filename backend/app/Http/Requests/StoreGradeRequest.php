<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Grade;
use Illuminate\Foundation\Http\FormRequest;

final class StoreGradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $classGroup = $this->route('classGroup');
        $student = $this->route('student');
        $subject = $this->route('subject');

        return $this->user()->can('create', [Grade::class, $classGroup, $student, $subject]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => ['required', 'decimal:0,2'],
            'max_value' => ['sometimes', 'required', 'decimal:0,2', 'gte:value'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:1024'],
            'coefficient' => ['sometimes', 'required', 'decimal:0,2'],
            'academic_year' => ['sometimes', 'required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ];
    }
}
