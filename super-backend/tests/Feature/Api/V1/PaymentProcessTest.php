<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\TransactionStatus;
use App\Models\User;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\DTOs\PaymentGatewayResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PaymentProcessTest extends TestCase
{
    use RefreshDatabase;

    private User $payer;
    private User $payee;
    private MockInterface $mockGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payer = User::factory()->withWallet(1000.00)->create();
        $this->payee = User::factory()->withWallet(500.00)->create();

        Sanctum::actingAs($this->payer);

        $this->mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $this->app->bind('gateway.subadquirente_a', fn () => $this->mockGateway);
    }

    public function test_user_can_process_payment_successfully(): void
    {
        $this->mockGateway->shouldReceive('charge')
            ->once()
            ->andReturn(new PaymentGatewayResponse(
                success: true,
                transactionId: 'gw_' . Str::uuid(),
                status: 'authorized',
            ));

        $payload = [
            'amount' => 100.00,
            'gateway_name' => 'subadquirente_a',
            'payment_token' => 'tok_valid',
            'payee_id' => $this->payee->getKey(),
        ];

        $response = $this->postJson('/api/v1/payment/process', $payload);

        $response->assertStatus(201)->assertJsonStructure([
            'message',
            'transaction_id',
            'status',
        ]);

        $this->assertDatabaseHas('transactions', [
            'payer_id' => $this->payer->getKey(),
            'payee_id' => $this->payee->getKey(),
            'status' => TransactionStatus::COMPLETED->value,
        ]);

        $this->assertDatabaseHas('payment_attempts', [
            'gateway_name' => 'subadquirente_a',
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->payer->getKey(),
            'balance' => 900.00,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->payee->getKey(),
            'balance' => 600.00,
        ]);
    }

    public function test_payment_fails_if_gateway_declines(): void
    {
        $this->mockGateway->shouldReceive('charge')
            ->once()
            ->andReturn(new PaymentGatewayResponse(
                success: false,
                transactionId: 'gw_failed_' . Str::uuid(),
                status: 'declined',
            ));

        $payload = [
            'amount' => 100.00,
            'gateway_name' => 'subadquirente_a',
            'payment_token' => 'tok_invalid',
            'payee_id' => $this->payee->getKey(),
        ];

        $response = $this->postJson('/api/v1/payment/process', $payload);

        $response->assertStatus(422)->assertJson([
            'message' => 'Falha ao processar o pagamento.',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->payer->getKey(),
            'balance' => 1000.00,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->payee->getKey(),
            'balance' => 500.00,
        ]);

        $this->assertDatabaseHas('transactions', [
            'payer_id' => $this->payer->getKey(),
            'payee_id' => $this->payee->getKey(),
            'status' => TransactionStatus::FAILED->value,
        ]);
    }
}
