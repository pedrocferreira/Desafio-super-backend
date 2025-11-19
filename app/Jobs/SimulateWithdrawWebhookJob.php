<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Withdraw;
use App\Services\Subadquirentes\SubadquirenteGatewayManager;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SimulateWithdrawWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Withdraw $withdraw,
        public string $gatewayName,
        public bool $forceFailure = false
    ) {
    }

    public function handle(
        SubadquirenteGatewayManager $gatewayManager,
        WebhookService $webhookService
    ): void {
        $gateway = $gatewayManager->resolve($this->gatewayName);
        $payload = $gateway->generateWithdrawWebhookPayload($this->withdraw, $this->forceFailure);

        $webhookService->handleWithdrawWebhook(
            $this->withdraw->fresh(),
            $payload['event_type'],
            $payload['payload'],
            $this->gatewayName
        );
    }
}
