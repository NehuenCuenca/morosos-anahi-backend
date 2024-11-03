<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDebtRequest extends FormRequest
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
            "new_defaulter_name" => "filled|max:50",
            "new_thing_name" => "filled|max:50",
            "defaulter_id" => "exists:App\Models\Defaulter,id",
            "thing_id" => "exists:App\Models\Thing,id",
            "unit_price" => "integer|numeric",
            "quantity" => "integer|numeric",
            "retired_at" => "date_format:Y-m-d",
            "filed_at" => "nullable|date_format:Y-m-d",
            "was_paid" => "boolean",
        ];
    }
}
