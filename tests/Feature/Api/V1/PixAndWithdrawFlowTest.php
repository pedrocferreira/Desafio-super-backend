<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PixAndWithdrawFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_pix_and_receive_balance(): void
    {
        Http::fake([
            '*' => Http::response([
                'pix_id' => 'PIX123',
                'transaction_id' => 'TX123',
                'qr_code' => 'qr-code',
                'qr_code_base64' => 'base64data',
            ], 200),
        ]);

        $user = User::factory()
            ->withWallet(1000.00)
            ->withSubadquirente()
            ->create();

        Sanctum::actingAs($user);

        $payload = [
            'amount' => 150.50,
            'payer_name' => 'Cliente Teste',
            'payer_document' => '11144477735',
            'description' => 'Pedido #123',
        ];

        $response = $this->postJson('/api/v1/pix', $payload);

        $response->assertCreated();

        $this->assertEquals($payload['amount'], (float) $response->json('data.amount'));

        $this->assertDatabaseHas('pix', [
            'user_id' => $user->getKey(),
            'amount' => $payload['amount'],
        ]);

        $user->refresh();
        $this->assertSame(1150.5, (float) $user->wallet->balance);
    }

    public function test_user_can_request_withdraw_and_balance_is_reserved(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'id' => 'WDX54321',
                    'status' => 'DONE',
                ],
                'transaction_id' => 'TRX-001',
            ], 200),
        ]);

        $user = User::factory()
            ->withWallet(500.00)
            ->withSubadquirente()
            ->create();

        Sanctum::actingAs($user);

        $payload = [
            'amount' => 200,
            'bank_account' => [
                'bank' => 'Nubank',
                'agency' => '0001',
                'account' => '1234567-8',
                'account_type' => 'checking',
            ],
        ];

        $response = $this->postJson('/api/v1/withdraws', $payload);

        $response->assertCreated();

        $this->assertEquals($payload['amount'], (float) $response->json('data.amount'));

        $this->assertDatabaseHas('withdraws', [
            'user_id' => $user->getKey(),
            'amount' => $payload['amount'],
        ]);

        $user->refresh();
        $this->assertSame(300.0, (float) $user->wallet->balance);
    }
}

