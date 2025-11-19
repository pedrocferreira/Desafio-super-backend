<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransactionService
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function createPayment(
        User $payer,
        float $amount,
        string $gatewayName,
        string $paymentToken,
        User $payee,
    ): Transaction {
        [$transaction, $success, $gatewayStatus] = DB::transaction(function () use (
            $payer,
            $amount,
            $gatewayName,
            $paymentToken,
            $payee,
        ) {
            $payerWallet = $this->lockWallet($payer->wallet);
            $payeeWallet = $this->lockWallet($payee->wallet);

            if ($payerWallet->balance < $amount) {
                throw new RuntimeException('Saldo insuficiente.');
            }

            $transaction = Transaction::query()->create([
                'amount' => $amount,
                'status' => TransactionStatus::PENDING,
                'type' => TransactionType::PAYMENT,
                'payer_id' => $payer->getKey(),
                'payee_id' => $payee->getKey(),
                'payment_gateway' => $gatewayName,
            ]);

            $response = $this->paymentService->processPayment(
                $amount,
                $paymentToken,
                $gatewayName,
            );

            $transaction->paymentAttempts()->create([
                'gateway_name' => $gatewayName,
                'gateway_response' => $response->metadata,
                'status' => $response->success ? 'success' : 'failure',
            ]);

            if ($response->success) {
                $this->updateWallets($payerWallet, $payeeWallet, $amount);
                $transaction->status = TransactionStatus::COMPLETED;
            } else {
                $transaction->status = TransactionStatus::FAILED;
            }

            $transaction->save();

            return [$transaction, $response->success, $response->status];
        });

        if (! $success) {
            throw new RuntimeException('Falha no gateway de pagamento: ' . $gatewayStatus);
        }

        return $transaction;
    }

    private function updateWallets(Wallet $payerWallet, Wallet $payeeWallet, float $amount): void
    {
        $payerWallet->balance = (float) $payerWallet->balance - $amount;
        $payerWallet->save();

        $payeeWallet->balance = (float) $payeeWallet->balance + $amount;
        $payeeWallet->save();
    }

    private function lockWallet(?Wallet $wallet): Wallet
    {
        if ($wallet === null) {
            throw new RuntimeException('Carteira não encontrada.');
        }

        return Wallet::query()
            ->whereKey($wallet->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }
}
