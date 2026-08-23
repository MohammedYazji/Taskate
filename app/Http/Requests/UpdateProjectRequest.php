<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'color' => ['sometimes', 'required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'view_type' => 'nullable|string|in:list,kanban,timeline',
            'folder_id' => ['nullable', 'integer', Rule::exists('folders', 'id')->where('user_id', $this->user()->id)],
        ];
    }
}
