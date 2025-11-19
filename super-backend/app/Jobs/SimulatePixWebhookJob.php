<?php

namespace App\Jobs;

use App\Models\Pix;
use App\Services\Subadquirentes\SubadquirenteGatewayManager;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SimulatePixWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Pix $pix,
        public string $gatewayName,
        public bool $forceFailure = false
    ) {
    }

    public function handle(
        SubadquirenteGatewayManager $gatewayManager,
        WebhookService $webhookService
    ): void {
        $gateway = $gatewayManager->resolve($this->gatewayName);
        $payload = $gateway->generatePixWebhookPayload($this->pix, $this->forceFailure);

        $webhookService->handlePixWebhook(
            $this->pix->fresh(),
            $payload['event_type'],
            $payload['payload'],
            $this->gatewayName
        );
    }
}
