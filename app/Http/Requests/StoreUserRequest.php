<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class StoreUserRequest extends FormRequest
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
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:25',
                'regex:/^[\p{L}0-9_\s]{3,25}$/u',
                'unique:users'
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:320',
                'unique:users'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).{6,}$/'
            ],
            'role' => 'required|integer|in:1,2'
        ];
    }
}