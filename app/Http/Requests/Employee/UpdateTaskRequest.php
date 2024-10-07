<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url',
            'status' => 'nullable|in:In Progress,Done',
            'date_added' => 'nullable|date',
            'date_finished' => 'nullable|date|after_or_equal:date_added',
            'hours_worked' => 'nullable|numeric|min:0',
            'pending_days' => 'nullable|numeric|min:0',
        ];
    }
}
