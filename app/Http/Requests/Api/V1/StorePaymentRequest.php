<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'gateway_name' => ['required', 'string', 'in:subadquirente_a,subadquirente_b'],
            'payment_token' => ['required', 'string', 'max:255'],
            'payee_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
