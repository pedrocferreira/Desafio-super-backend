<?php

declare(strict_types=1);

namespace App\Services\Payments\Contracts;

use App\Services\Payments\DTOs\PaymentGatewayResponse;

interface PaymentGatewayInterface
{
    public function charge(float $amount, string $paymentToken): PaymentGatewayResponse;

    public function refund(string $transactionId, float $amount): PaymentGatewayResponse;
}
