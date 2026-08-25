<?php

namespace App\Http\Requests;

use App\Enums\Importance;
use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
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
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->whereIn(
                'id',
                Project::forUser($this->user()->id)->pluck('id')
            )],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')->whereIn(
                'project_id',
                Project::forUser($this->user()->id)->pluck('id')
            )],
            'assigned_to_id' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                if (!$value) return;
                $project = $this->input('project_id') ? Project::find($this->input('project_id')) : null;
                $assignee = User::find($value);
                if (!$project || !$assignee || !$project->hasMember($assignee)) {
                    $fail('The assignee must be a member of this project.');
                }
            }],
        ];
    }
}
