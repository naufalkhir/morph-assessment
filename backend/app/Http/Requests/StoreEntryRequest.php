<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
{
    // authorize() — return true means anyone can make this request
    // If we had authentication, we'd check user roles here
    public function authorize(): bool
    {
        return true;
    }

    // rules() — defines validation rules for each field
    // Laravel runs these BEFORE the controller method is called
    public function rules(): array
    {
        return [
            // required = must exist, string = text only, max:255 = database column limit
            'description' => 'required|string|max:255',

            // numeric = must be a number, min:0.01 = no zero or negative, max = prevent overflow
            'amount' => 'required|numeric|min:0.01|max:999999999.99',

            // size:3 = exactly 3 characters — enforces ISO currency code format (MYR, USD, EUR)
            'currency' => 'required|string|size:3',

            // date_format:Y-m-d = must be YYYY-MM-DD format exactly
            'transaction_date' => 'required|date|date_format:Y-m-d',
        ];
    }

    // messages() — custom error messages instead of Laravel's default ones
    // These are what get returned in the 422 response errors object
    public function messages(): array
    {
        return [
            'description.required' => 'A description is required.',
            'amount.required' => 'An amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than 0.',
            'currency.required' => 'A currency is required.',
            'currency.size' => 'Currency must be a 3-letter ISO code (e.g. MYR).',
            'transaction_date.required' => 'A transaction date is required.',
            'transaction_date.date' => 'Transaction date must be a valid date.',
        ];
    }

    // prepareForValidation() — runs BEFORE validation
    // Normalises currency to uppercase so 'myr' and 'MYR' both pass size:3
    protected function prepareForValidation(): void
    {
        if ($this->currency) {
            $this->merge(['currency' => strtoupper($this->currency)]);
        }
    }
}
