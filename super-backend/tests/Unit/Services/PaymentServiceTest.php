<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\DTOs\PaymentGatewayResponse;
use App\Services\Payments\PaymentService;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    private MockInterface $appMock;
    private MockInterface $gatewayMock;
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appMock = Mockery::mock(Application::class);
        $this->gatewayMock = Mockery::mock(PaymentGatewayInterface::class);
        $this->paymentService = new PaymentService($this->appMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_payment_service_resolves_and_calls_correct_gateway_strategy(): void
    {
        $gatewayName = 'gateway_test';
        $gatewayKey = 'gateway.' . $gatewayName;
        $response = new PaymentGatewayResponse(true, 'gw_123', 'success');

        $this->appMock->shouldReceive('bound')
            ->with($gatewayKey)
            ->once()
            ->andReturn(true);

        $this->appMock->shouldReceive('make')
            ->with($gatewayKey)
            ->once()
            ->andReturn($this->gatewayMock);

        $this->gatewayMock->shouldReceive('charge')
            ->with(100.50, 'tok_test')
            ->once()
            ->andReturn($response);

        $result = $this->paymentService->processPayment(100.50, 'tok_test', $gatewayName);

        self::assertSame($response, $result);
    }

    public function test_payment_service_throws_exception_for_invalid_gateway(): void
    {
        $gatewayName = 'invalid_gateway';
        $gatewayKey = 'gateway.' . $gatewayName;

        $this->appMock->shouldReceive('bound')
            ->with($gatewayKey)
            ->once()
            ->andReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway de pagamento inválido: invalid_gateway');

        $this->paymentService->processPayment(50.00, 'tok_invalid', $gatewayName);
    }
}
