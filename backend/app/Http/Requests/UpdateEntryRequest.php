<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'      => 'sometimes|required|string|max:255',
            'amount'           => 'sometimes|required|numeric|min:0.01|max:999999999.99',
            'currency'         => 'sometimes|required|string|size:3',
            'transaction_date' => 'sometimes|required|date|date_format:Y-m-d',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->currency) {
            $this->merge(['currency' => strtoupper($this->currency)]);
        }
    }
}
