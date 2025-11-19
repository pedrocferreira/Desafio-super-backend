<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PixStatus;
use App\Enums\WithdrawStatus;
use App\Models\Pix;
use App\Models\Webhook;
use App\Models\Withdraw;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WebhookService
{
    public function handlePixWebhook(Pix $pix, string $eventType, array $payload, string $subadquirente): void
    {
        DB::transaction(function () use ($pix, $eventType, $payload, $subadquirente) {
            Webhook::query()->create([
                'pix_id' => $pix->getKey(),
                'subadquirente' => $subadquirente,
                'event_type' => $eventType,
                'payload' => $payload,
                'processed' => true,
                'processed_at' => now(),
            ]);

            $status = $this->resolvePixStatus($payload);
            $pix->status = $status;
            $pix->payment_date = $this->extractDate($payload);
            $pix->save();

            if (in_array($status, [PixStatus::CONFIRMED, PixStatus::PAID], true)) {
                $wallet = $pix->user->wallet()->lockForUpdate()->firstOrFail();
                $wallet->balance = (float) $wallet->balance + (float) $pix->amount;
                $wallet->save();
            }
        });
    }

    public function handleWithdrawWebhook(Withdraw $withdraw, string $eventType, array $payload, string $subadquirente): void
    {
        DB::transaction(function () use ($withdraw, $eventType, $payload, $subadquirente) {
            Webhook::query()->create([
                'withdraw_id' => $withdraw->getKey(),
                'subadquirente' => $subadquirente,
                'event_type' => $eventType,
                'payload' => $payload,
                'processed' => true,
                'processed_at' => now(),
            ]);

            $status = $this->resolveWithdrawStatus($payload);
            $withdraw->status = $status;
            $withdraw->processed_at = $withdraw->processed_at ?? now();
            if (in_array($status, [WithdrawStatus::SUCCESS, WithdrawStatus::DONE], true)) {
                $withdraw->completed_at = now();
            }
            $withdraw->save();

            if (in_array($status, [WithdrawStatus::FAILED, WithdrawStatus::CANCELLED], true)) {
                $wallet = $withdraw->user->wallet()->lockForUpdate()->firstOrFail();
                $wallet->balance = (float) $wallet->balance + (float) $withdraw->amount;
                $wallet->save();
            }
        });
    }

    private function resolvePixStatus(array $payload): PixStatus
    {
        $status = strtoupper((string) Arr::get($payload, 'status', Arr::get($payload, 'data.status', 'PENDING')));

        return match ($status) {
            'CONFIRMED' => PixStatus::CONFIRMED,
            'PAID' => PixStatus::PAID,
            'FAILED' => PixStatus::FAILED,
            'CANCELLED' => PixStatus::CANCELLED,
            default => PixStatus::PROCESSING,
        };
    }

    private function resolveWithdrawStatus(array $payload): WithdrawStatus
    {
        $status = strtoupper((string) Arr::get($payload, 'status', Arr::get($payload, 'data.status', 'PENDING')));

        return match ($status) {
            'SUCCESS' => WithdrawStatus::SUCCESS,
            'DONE' => WithdrawStatus::DONE,
            'FAILED' => WithdrawStatus::FAILED,
            'CANCELLED' => WithdrawStatus::CANCELLED,
            'PROCESSING' => WithdrawStatus::PROCESSING,
            default => WithdrawStatus::PENDING,
        };
    }

    private function extractDate(array $payload): ?string
    {
        return Arr::get($payload, 'payment_date')
            ?? Arr::get($payload, 'data.confirmed_at')
            ?? null;
    }
}

