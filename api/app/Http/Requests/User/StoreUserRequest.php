<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:120', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'type' => ['sometimes', 'string', Rule::in(['admin', 'staff'])],
        ];
    }
}
