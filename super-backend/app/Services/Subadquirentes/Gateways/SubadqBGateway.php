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

class SubadqBGateway implements SubadquirenteGatewayInterface
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('subadquirentes.subadq_b', []);
    }

    public function getName(): string
    {
        return 'subadq_b';
    }

    public function createPix(array $payload): array
    {
        $simulateFailure = (bool) ($payload['simulate_failure'] ?? false);
        $headers = [
            'x-mock-response-name' => $simulateFailure
                ? Arr::get($this->config, 'responses.pix_error')
                : Arr::get($this->config, 'responses.pix_success'),
        ];

        // Formato esperado pelo Postman Mock (SubadqB)
        $requestPayload = [
            'seller_id' => 'm123', // SubadqB usa "seller_id" ao invés de "merchant_id"
            'amount' => (int) ($payload['amount'] * 100), // Postman espera em centavos
            'order' => 'order_' . Str::random(8), // SubadqB usa "order" ao invés de "order_id"
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
            Log::warning('Mock SubadqB não disponível, usando fallback', [
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

        // Formato esperado pelo Postman Mock (SubadqB - mesmo formato do SubadqA)
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
                    $status = Arr::get($data, 'status', 'DONE');
                    
                    return [
                        'withdraw_id' => $withdrawId,
                        'transaction_id' => $requestPayload['transaction_id'], // Usa o que enviamos
                        'status' => strtolower($status) === 'done' ? 'pending' : 'pending',
                        'metadata' => array_merge($data, ['mock_used' => true]),
                    ];
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Mock SubadqB não disponível, usando fallback', [
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
        $status = $forceFailure ? 'FAILED' : 'PAID';

        return [
            'event_type' => 'pix.status_update',
            'payload' => [
                'type' => 'pix.status_update',
                'data' => [
                    'id' => $pix->pix_id,
                    'status' => $status,
                    'value' => (float) $pix->amount,
                    'payer' => [
                        'name' => $pix->payer_name,
                        'document' => $pix->payer_document,
                    ],
                    'confirmed_at' => now()->toIso8601String(),
                ],
                'signature' => Str::random(12),
            ],
        ];
    }

    public function generateWithdrawWebhookPayload(Withdraw $withdraw, bool $forceFailure = false): array
    {
        $status = $forceFailure ? 'FAILED' : 'DONE';

        return [
            'event_type' => 'withdraw.status_update',
            'payload' => [
                'type' => 'withdraw.status_update',
                'data' => [
                    'id' => $withdraw->withdraw_id,
                    'status' => $status,
                    'amount' => (float) $withdraw->amount,
                    'bank_account' => $withdraw->bank_account,
                    'processed_at' => now()->toIso8601String(),
                ],
                'signature' => Str::random(18),
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
            throw new \RuntimeException('SubadqB: Erro ao gerar PIX (simulado).');
        }

        $pixId = 'PX' . Str::random(9) . time();
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
                'source' => 'SubadqB',
                'environment' => 'sandbox',
            ],
        ];
    }

    private function generateFallbackWithdrawResponse(array $payload, bool $simulateFailure): array
    {
        if ($simulateFailure) {
            throw new \RuntimeException('SubadqB: Erro ao solicitar saque (simulado).');
        }

        $withdrawId = 'WDX' . Str::random(5) . time();
        $transactionId = 'TXN_' . Str::random(10) . '_' . time();

        return [
            'withdraw_id' => $withdrawId,
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'metadata' => [
                'mock_used' => false,
                'fallback' => true,
                'source' => 'SubadqB',
                'environment' => 'sandbox',
            ],
        ];
    }
}

