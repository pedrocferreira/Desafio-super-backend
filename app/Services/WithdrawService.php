<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubadquirenteType;
use App\Enums\WithdrawStatus;
use App\Jobs\SimulateWithdrawWebhookJob;
use App\Models\User;
use App\Models\UserSubadquirente;
use App\Models\Withdraw;
use App\Services\Subadquirentes\SubadquirenteGatewayManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WithdrawService
{
    public function __construct(
        private readonly SubadquirenteGatewayManager $gatewayManager,
    ) {
    }

    public function create(User $user, array $payload): Withdraw
    {
        $subadquirente = $this->resolveSubadquirenteType($user, $payload['subadquirente'] ?? null);
        $gateway = $this->gatewayManager->resolve($subadquirente->value);

        $gatewayResponse = $gateway->createWithdraw($payload);

        $withdraw = DB::transaction(function () use ($user, $payload, $subadquirente, $gatewayResponse) {
            $wallet = $user->wallet()->lockForUpdate()->firstOrFail();

            if ((float) $wallet->balance < (float) $payload['amount']) {
                throw new RuntimeException('Saldo insuficiente para saque.');
            }

            $wallet->balance = (float) $wallet->balance - (float) $payload['amount'];
            $wallet->save();

            return Withdraw::query()->create([
                'user_id' => $user->getKey(),
                'subadquirente' => $subadquirente->value,
                'withdraw_id' => $gatewayResponse['withdraw_id'],
                'transaction_id' => $gatewayResponse['transaction_id'],
                'amount' => $payload['amount'],
                'status' => WithdrawStatus::PENDING,
                'bank_account' => $payload['bank_account'],
                'metadata' => $gatewayResponse['metadata'] ?? [],
                'requested_at' => now(),
            ]);
        });

        SimulateWithdrawWebhookJob::dispatch(
            $withdraw,
            $gateway->getName(),
            (bool) Arr::get($payload, 'simulate_failure', false)
        )->delay(now()->addSeconds((int) config('subadquirentes.webhook_delay_seconds', 3)));

        return $withdraw->fresh();
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

