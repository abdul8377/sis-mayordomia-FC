<?php

namespace App\Modules\People\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin', 'gp_leader');
    }

    public function rules(): array
    {
        return ['full_name' => ['required', 'string', 'max:150'], 'phone' => ['nullable', 'string', 'max:25'], 'email' => ['nullable', 'email', 'max:255'], 'status' => ['required', 'in:active,inactive'], 'group_id' => ['nullable', 'integer', 'exists:small_groups,id']];
    }
}
