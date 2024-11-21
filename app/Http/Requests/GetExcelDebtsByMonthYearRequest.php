<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetExcelDebtsByMonthYearRequest extends FormRequest
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
            'month' => 'integer|between:1,12|nullable',
            'year' => 'integer|between:1900,3000|nullable'
        ];
    }
}

