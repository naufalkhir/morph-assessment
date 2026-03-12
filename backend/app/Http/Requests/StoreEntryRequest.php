<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01|max:999999999.99',
            'currency'         => 'required|string|size:3',
            'transaction_date' => 'required|date|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required'      => 'A description is required.',
            'amount.required'           => 'An amount is required.',
            'amount.numeric'            => 'Amount must be a number.',
            'amount.min'                => 'Amount must be greater than 0.',
            'currency.required'         => 'A currency is required.',
            'currency.size'             => 'Currency must be a 3-letter ISO code (e.g. MYR).',
            'transaction_date.required' => 'A transaction date is required.',
            'transaction_date.date'     => 'Transaction date must be a valid date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->currency) {
            $this->merge(['currency' => strtoupper($this->currency)]);
        }
    }
}
