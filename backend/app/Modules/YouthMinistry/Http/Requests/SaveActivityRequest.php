<?php

namespace App\Modules\YouthMinistry\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin', 'ja_director');
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:180'], 'description' => ['nullable', 'string', 'max:3000'], 'place' => ['nullable', 'string', 'max:255'], 'activity_type_id' => ['required', 'integer', 'exists:activity_types,id'], 'starts_at' => ['required', 'date'], 'ends_at' => ['required', 'date', 'after:starts_at'], 'status' => ['required', 'in:draft,published,cancelled'], 'is_primary' => ['required', 'boolean'], 'group_id' => ['nullable', 'integer', 'exists:small_groups,id']];
    }
}
