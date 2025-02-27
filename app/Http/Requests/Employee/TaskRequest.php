<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'link' => 'nullable|url',
            'status' => 'nullable|in:In Progress,Done,Suspended,Cancelled',
            'date_added' => 'nullable|date',
            'date_finished' => 'nullable|date|after_or_equal:date_added',
            'hours_worked' => 'nullable|numeric|min:0',
            'pending_days' => 'nullable|numeric|min:0',
            'paps' => 'nullable|string',
            'type' => 'nullable|string',
            'task' => 'nullable|string',
            'no_of_document' => 'nullable|integer',
            'document_links' => 'nullable|array',
            'document_links.*' => 'nullable|url',
        ];
    }
}
