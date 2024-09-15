<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            "defaulter_id" => "required|max:50", 
            "name" => "required|max:50", 
            "unit_price" => "required", 
            "quantity" => "required", 
            "retirement_date" => "required",
            "was_paid" => "required"
        ];
    }
}
