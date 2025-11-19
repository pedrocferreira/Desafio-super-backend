<?php

declare(strict_types=1);

namespace App\Services\Subadquirentes\Gateways;

use App\Models\Pix;
use App\Models\Withdraw;
use App\Services\Subadquirentes\Contracts\SubadquirenteGatewayInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SubadqAGateway implements SubadquirenteGatewayInterface
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('subadquirentes.subadq_a', []);
    }

    public function getName(): string
    {
        return 'subadq_a';
    }

    public function createPix(array $payload): array
    {
        $simulateFailure = (bool) ($payload['simulate_failure'] ?? false);
        $headers = [
            'x-mock-response-name' => $simulateFailure
                ? Arr::get($this->config, 'responses.pix_error')
                : Arr::get($this->config, 'responses.pix_success'),
        ];

        // Formato esperado pelo Postman Mock
        $requestPayload = [
            'merchant_id' => 'm123', // Valor padrão do mock
            'amount' => (int) ($payload['amount'] * 100), // Postman espera em centavos
            'currency' => 'BRL',
            'order_id' => 'order_' . Str::random(8),
            'payer' => [
                'name' => $payload['payer_name'] ?? 'Cliente',
                'cpf_cnpj' => $payload['payer_document'] ?? '00000000000',
            ],
            'expires_in' => 3600,
        ];

        try {
            $response = $this->client($headers)
                ->timeout(5)
                ->post('/pix/create', $requestPayload);

            if ($response->successful()) {
                $data = $response->json() ?? [];
                
                // Verifica se recebeu dados válidos do mock
                if (!empty($data) && !isset($data['error'])) {
                    // Postman retorna: transaction_id, location, qrcode, expires_at, status
                    $transactionId = Arr::get($data, 'transaction_id', Str::uuid()->toString());
                    $qrCode = Arr::get($data, 'qrcode'); // Note: "qrcode" não "qr_code"
                    $location = Arr::get($data, 'location');
                    
                    // Gera pix_id baseado no transaction_id ou cria novo
                    $pixId = $transactionId ?: Str::uuid()->toString();
                    
                    // Garante que sempre tenha QR code
                    if (empty($qrCode)) {
                        $qrCode = '00020126580014BR.GOV.BCB.PIX0136' . Str::random(40);
                    }
                    
                    return [
                        'pix_id' => $pixId,
                        'transaction_id' => $transactionId,
                        'status' => 'pending',
                        'qr_code' => $qrCode,
                        'qr_code_base64' => base64_encode($qrCode), // Gera base64 do qrcode
                        'metadata' => array_merge($data, [
                            'mock_used' => true,
                            'location' => $location,
                            'expires_at' => Arr::get($data, 'expires_at'),
                        ]),
                    ];
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Mock SubadqA não disponível, usando fallback', [
                'error' => $exception->getMessage(),
                'endpoint' => '/pix/create',
            ]);
        }

        // Fallback: gera resposta simulada quando mock não está disponível
        return $this->generateFallbackPixResponse($payload, $simulateFailure);
    }

    public function createWithdraw(array $payload): array
    {
        $simulateFailure = (bool) ($payload['simulate_failure'] ?? false);
        $headers = [
            'x-mock-response-name' => $simulateFailure
                ? Arr::get($this->config, 'responses.withdraw_error')
                : Arr::get($this->config, 'responses.withdraw_success'),
        ];

        // Formato esperado pelo Postman Mock
        $requestPayload = [
            'merchant_id' => 'm123', // Valor padrão do mock
            'account' => [
                'bank_code' => $this->extractBankCode($payload['bank_account']['bank'] ?? '001'),
                'agencia' => $payload['bank_account']['agency'] ?? '0001',
                'conta' => $payload['bank_account']['account'] ?? '00000000',
                'type' => $payload['bank_account']['account_type'] ?? 'checking',
            ],
            'amount' => (int) ($payload['amount'] * 100), // Postman espera em centavos
            'transaction_id' => 'SP_' . Str::uuid()->toString(),
        ];

        try {
            $response = $this->client($headers)
                ->timeout(5)
                ->post('/withdraw', $requestPayload);

            if ($response->successful()) {
                $data = $response->json() ?? [];
                
                // Verifica se recebeu dados válidos do mock
                if (!empty($data) && !isset($data['error'])) {
                    // Postman retorna: withdraw_id, status
                    $withdrawId = Arr::get($data, 'withdraw_id', Str::uuid()->toString());
                    $status = Arr::get($data, 'status', 'PROCESSING');
                    
                    return [
                        'withdraw_id' => $withdrawId,
                        'transaction_id' => $requestPayload['transaction_id'], // Usa o que enviamos
                        'status' => strtolower($status) === 'processing' ? 'pending' : 'pending',
                        'metadata' => array_merge($data, ['mock_used' => true]),
                    ];
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Mock SubadqA não disponível, usando fallback', [
                'error' => $exception->getMessage(),
                'endpoint' => '/withdraw',
            ]);
        }

        // Fallback: gera resposta simulada quando mock não está disponível
        return $this->generateFallbackWithdrawResponse($payload, $simulateFailure);
    }

    private function extractBankCode(?string $bankName): string
    {
        if (empty($bankName)) {
            return '001';
        }

        // Mapeia nomes comuns de bancos para códigos
        $bankCodes = [
            'nubank' => '260',
            'itau' => '341',
            'bradesco' => '237',
            'banco do brasil' => '001',
            'santander' => '033',
        ];

        $bankLower = strtolower($bankName);
        foreach ($bankCodes as $name => $code) {
            if (str_contains($bankLower, $name)) {
                return $code;
            }
        }

        return '001'; // Default
    }

    public function generatePixWebhookPayload(Pix $pix, bool $forceFailure = false): array
    {
        $status = $forceFailure ? 'FAILED' : 'CONFIRMED';

        return [
            'event_type' => 'pix_payment_confirmed',
            'payload' => [
                'event' => 'pix_payment_confirmed',
                'transaction_id' => $pix->transaction_id,
                'pix_id' => $pix->pix_id,
                'status' => $status,
                'amount' => (float) $pix->amount,
                'payer_name' => $pix->payer_name,
                'payer_cpf' => $pix->payer_document,
                'payment_date' => now()->toIso8601String(),
                'metadata' => [
                    'source' => 'SubadqA',
                    'environment' => 'sandbox',
                ],
            ],
        ];
    }

    public function generateWithdrawWebhookPayload(Withdraw $withdraw, bool $forceFailure = false): array
    {
        $status = $forceFailure ? 'FAILED' : 'SUCCESS';

        return [
            'event_type' => 'withdraw_completed',
            'payload' => [
                'event' => 'withdraw_completed',
                'withdraw_id' => $withdraw->withdraw_id,
                'transaction_id' => $withdraw->transaction_id,
                'status' => $status,
                'amount' => (float) $withdraw->amount,
                'requested_at' => $withdraw->requested_at->toIso8601String(),
                'completed_at' => now()->toIso8601String(),
                'metadata' => [
                    'source' => 'SubadqA',
                    'destination_bank' => Arr::get($withdraw->bank_account, 'bank'),
                ],
            ],
        ];
    }

    private function client(array $headers = []): PendingRequest
    {
        $baseHeaders = Arr::get($this->config, 'default_headers', []);
        $baseUrl = Arr::get($this->config, 'base_url', 'https://example.org');

        return Http::baseUrl($baseUrl)
            ->withHeaders(array_filter([...$baseHeaders, ...$headers]));
    }

    private function generateFallbackPixResponse(array $payload, bool $simulateFailure): array
    {
        if ($simulateFailure) {
            throw new \RuntimeException('SubadqA: Erro ao gerar PIX (simulado).');
        }

        $pixId = 'PIX_' . Str::random(10) . '_' . time();
        $transactionId = 'TXN_' . Str::random(10) . '_' . time();

        return [
            'pix_id' => $pixId,
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'qr_code' => '00020126580014BR.GOV.BCB.PIX0136' . Str::random(40),
            'qr_code_base64' => base64_encode('QR_CODE_' . $pixId),
            'metadata' => [
                'mock_used' => false,
                'fallback' => true,
                'source' => 'SubadqA',
                'environment' => 'sandbox',
            ],
        ];
    }

    private function generateFallbackWithdrawResponse(array $payload, bool $simulateFailure): array
    {
        if ($simulateFailure) {
            throw new \RuntimeException('SubadqA: Erro ao solicitar saque (simulado).');
        }

        $withdrawId = 'WD_' . Str::random(10) . '_' . time();
        $transactionId = 'TXN_' . Str::random(10) . '_' . time();

        return [
            'withdraw_id' => $withdrawId,
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'metadata' => [
                'mock_used' => false,
                'fallback' => true,
                'source' => 'SubadqA',
                'environment' => 'sandbox',
            ],
        ];
    }
}

