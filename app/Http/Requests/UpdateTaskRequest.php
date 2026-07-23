<?php

namespace App\Http\Requests;

use App\Enums\Importance;
use App\Enums\Priority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean|nullable',
            'priority' => [new Enum(Priority::class)],
            'importance' => ['nullable', new Enum(Importance::class)],
            'status' => [new Enum(TaskStatus::class)],
            'due_date' => 'nullable|date',
            'project_id' => 'nullable|integer|exists:projects,id',
        ];
    }
}
