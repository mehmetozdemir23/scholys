<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class ImportUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('import', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'users' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }
}
