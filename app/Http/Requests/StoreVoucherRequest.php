<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
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
            "customer" => "min:3",
            "phone" => "min:6",
            "voucher_records" => "array|required",
            'voucher_records.*.product_id' => 'required|numeric|exists:products,id',
            'voucher_records.*.quantity' => 'required|numeric|min:1',
        ];
    }
}
