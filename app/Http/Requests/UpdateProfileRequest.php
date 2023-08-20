<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "name" => "min:3",
            "phone" => "numeric|min:9",
            "date_of_birth" => "date",
            "gender" => "in:male,female",
            "status" => "in:active,ban",
            "address" => "min:50",
            "email" => "email|unique:users",
            "user_photo" => "url",
            "password" => "min:8|confirmed"
        ];
    }
}
