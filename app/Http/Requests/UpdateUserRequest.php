<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateUserRequest extends FormRequest
{
    

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return 2;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('user');
        $primaryKey = 'user_id';

        return [
            'name' => [
                'sometimes',
                'string',
                'regex:/\S/',
                'max:25',
                'min:3',
                Rule::unique('users')->ignore($id, $primaryKey),
            ],
            'email' => [
                'sometimes',
                'regex:/\S/',
                'email',
                'max:320',
                Rule::unique('users')->ignore($id, $primaryKey),
            ],
            'password' => 'sometimes|regex:/\S/|string|min:6',
        ];
    }
}