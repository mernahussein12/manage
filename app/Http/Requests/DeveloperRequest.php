<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeveloperRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_name' => 'required|string',
            'project_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'project_leader' => 'required|exists:users,id',
            // 'team' => ['required', 'array'], // يجب أن يكون مصفوفة
            // 'team.*' => ['exists:users,id'],
            'support' => 'required|exists:users,id',
           'summary' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'cost' => 'required|numeric',
            'profit_margin' => 'required|numeric',
        ];
    }
}

