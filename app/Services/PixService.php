<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PixStatus;
use App\Enums\SubadquirenteType;
use App\Jobs\SimulatePixWebhookJob;
use App\Models\Pix;
use App\Models\User;
use App\Models\UserSubadquirente;
use App\Services\Subadquirentes\SubadquirenteGatewayManager;
use Illuminate\Support\Arr;

class PixService
{
    public function __construct(
        private readonly SubadquirenteGatewayManager $gatewayManager,
    ) {
    }

    public function create(User $user, array $payload): Pix
    {
        $subadquirente = $this->resolveSubadquirenteType($user, $payload['subadquirente'] ?? null);
        $gateway = $this->gatewayManager->resolve($subadquirente->value);

        $gatewayResponse = $gateway->createPix($payload);

        $pix = Pix::query()->create([
            'user_id' => $user->getKey(),
            'subadquirente' => $subadquirente->value,
            'pix_id' => $gatewayResponse['pix_id'],
            'transaction_id' => $gatewayResponse['transaction_id'],
            'amount' => $payload['amount'],
            'status' => PixStatus::PENDING,
            'payer_name' => $payload['payer_name'] ?? null,
            'payer_document' => $payload['payer_document'] ?? null,
            'description' => $payload['description'] ?? null,
            'qr_code' => $gatewayResponse['qr_code'] ?? null,
            'qr_code_base64' => $gatewayResponse['qr_code_base64'] ?? null,
            'metadata' => $gatewayResponse['metadata'] ?? [],
        ]);

        SimulatePixWebhookJob::dispatch(
            $pix,
            $gateway->getName(),
            (bool) Arr::get($payload, 'simulate_failure', false)
        )->delay(now()->addSeconds((int) config('subadquirentes.webhook_delay_seconds', 3)));

        return $pix->fresh();
    }

    private function resolveSubadquirenteType(User $user, ?string $override): SubadquirenteType
    {
        if ($override !== null) {
            $type = SubadquirenteType::fromGatewayName($override);
            $this->ensureUserSubadquirente($user, $type);

            return $type;
        }

        $record = $user->subadquirentes()
            ->where('is_active', true)
            ->first();

        if ($record instanceof UserSubadquirente) {
            return SubadquirenteType::fromGatewayName($record->subadquirente);
        }

        $this->ensureUserSubadquirente($user, SubadquirenteType::SUBADQ_A);

        return SubadquirenteType::SUBADQ_A;
    }

    private function ensureUserSubadquirente(User $user, SubadquirenteType $type): void
    {
        UserSubadquirente::query()->updateOrCreate(
            [
                'user_id' => $user->getKey(),
                'subadquirente' => $type->value,
            ],
            ['is_active' => true]
        );
    }
}

