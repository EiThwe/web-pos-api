<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            "brand_id" => "numeric|exists:brands,id",
            "actual_price" => "numeric|min:100",
            "sale_price" => "numeric|min:100",
            "unit" => "string",
            "more_information" => "",
            "photo" => ""
        ];
    }
}
