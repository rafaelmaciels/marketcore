<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');
        return $this->user()->can('update', $user);
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password'      => ['nullable', 'string', 'min:6'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'status'        => ['required', 'in:active,inactive,blocked'],
            'role_id'       => ['nullable', 'exists:roles,id'],
            'all_branches'  => ['nullable', 'boolean'],
            'branches'      => ['nullable', 'array'],
            'branches.*.branch_id' => ['required_with:branches', 'exists:branches,id'],
            'branches.*.role_id'   => ['nullable', 'exists:roles,id'],
            'branches.*.is_default'=> ['nullable', 'boolean'],
        ];
    }
}
