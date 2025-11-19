<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'bank_account' => ['required', 'array'],
            'bank_account.bank' => ['required', 'string', 'max:120'],
            'bank_account.agency' => ['required', 'string', 'max:20'],
            'bank_account.account' => ['required', 'string', 'max:20'],
            'bank_account.account_type' => ['nullable', 'string', 'max:20'],
            'subadquirente' => ['nullable', 'string', 'in:subadq_a,subadq_b,subadquirente_a,subadquirente_b'],
            'simulate_failure' => ['sometimes', 'boolean'],
        ];
    }
}
