<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\DTOs\PaymentGatewayResponse;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(private readonly Application $app)
    {
    }

    public function processPayment(float $amount, string $paymentToken, string $gatewayName): PaymentGatewayResponse
    {
        $gateway = $this->resolveGateway($gatewayName);

        return $gateway->charge($amount, $paymentToken);
    }

    public function processRefund(string $transactionId, float $amount, string $gatewayName): PaymentGatewayResponse
    {
        $gateway = $this->resolveGateway($gatewayName);

        return $gateway->refund($transactionId, $amount);
    }

    private function resolveGateway(string $gatewayName): PaymentGatewayInterface
    {
        $gatewayKey = 'gateway.' . $gatewayName;

        if (! $this->app->bound($gatewayKey)) {
            throw new InvalidArgumentException("Gateway de pagamento inválido: {$gatewayName}");
        }

        /** @var PaymentGatewayInterface $gateway */
        $gateway = $this->app->make($gatewayKey);

        return $gateway;
    }
}
