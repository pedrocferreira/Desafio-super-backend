<?php

declare(strict_types=1);

namespace App\Services\Payments\Strategies;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\DTOs\PaymentGatewayResponse;
use Illuminate\Support\Str;

class SubAdquirenteAStrategy implements PaymentGatewayInterface
{
    public function charge(float $amount, string $paymentToken): PaymentGatewayResponse
    {
        $gatewayTransactionId = 'sub_a_' . Str::uuid();

        return new PaymentGatewayResponse(
            success: true,
            transactionId: $gatewayTransactionId,
            status: 'authorized',
            metadata: [
                'amount_authorized' => $amount,
                'payment_token' => $paymentToken,
            ],
        );
    }

    public function refund(string $transactionId, float $amount): PaymentGatewayResponse
    {
        return new PaymentGatewayResponse(
            success: true,
            transactionId: $transactionId,
            status: 'refunded',
            metadata: ['amount_refunded' => $amount],
        );
    }
}
