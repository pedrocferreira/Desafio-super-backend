<?php

declare(strict_types=1);

namespace App\Services\Subadquirentes\Contracts;

use App\Models\Pix;
use App\Models\Withdraw;

interface SubadquirenteGatewayInterface
{
    public function getName(): string;

    /**
     * @return array{
     *     pix_id:string,
     *     transaction_id:string,
     *     qr_code?:string|null,
     *     qr_code_base64?:string|null,
     *     status:string,
     *     metadata?:array|null
     * }
     */
    public function createPix(array $payload): array;

    /**
     * @return array{
     *     withdraw_id:string,
     *     transaction_id:string,
     *     status:string,
     *     metadata?:array|null
     * }
     */
    public function createWithdraw(array $payload): array;

    /**
     * Simula o payload bruto de webhook para PIX.
     *
     * @return array{event_type:string, payload:array}
     */
    public function generatePixWebhookPayload(Pix $pix, bool $forceFailure = false): array;

    /**
     * Simula o payload bruto de webhook para Saque.
     *
     * @return array{event_type:string, payload:array}
     */
    public function generateWithdrawWebhookPayload(Withdraw $withdraw, bool $forceFailure = false): array;
}

