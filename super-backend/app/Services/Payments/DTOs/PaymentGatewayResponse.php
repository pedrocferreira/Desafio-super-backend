<?php

declare(strict_types=1);

namespace App\Services\Payments\DTOs;

class PaymentGatewayResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly string $status,
        public readonly ?array $metadata = null,
    ) {
    }
}
