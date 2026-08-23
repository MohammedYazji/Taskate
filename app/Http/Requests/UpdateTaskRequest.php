<?php

namespace App\Http\Requests;

use App\Enums\Importance;
use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'title' => 'nullable|string|min:3|max:255',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean|nullable',
            'priority' => [new Enum(Priority::class)],
            'importance' => ['nullable', new Enum(Importance::class)],
            'status' => [new Enum(TaskStatus::class)],
            'due_date' => 'nullable|date',
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->where('user_id', $this->user()->id)],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')->whereIn(
                'project_id',
                Project::where('user_id', $this->user()->id)->pluck('id')
            )],
            'position' => 'nullable|integer',
        ];
    }
}
