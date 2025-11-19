<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Models\User;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @group Pagamentos
 * 
 * Endpoints para processamento de pagamentos usando múltiplos gateways (Strategy Pattern).
 */
class PaymentController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    /**
     * Processar Pagamento
     * 
     * Processa um pagamento usando um dos gateways disponíveis (subadquirente_a ou subadquirente_b).
     * O pagamento é processado de forma atômica, garantindo integridade dos dados.
     * 
     * **Gateways Disponíveis:**
     * - `subadquirente_a`: Simula sucesso no pagamento
     * - `subadquirente_b`: Simula falha no pagamento
     * 
     * **Fluxo:**
     * 1. Verifica saldo do pagador
     * 2. Cria transação com status PENDING
     * 3. Processa pagamento no gateway selecionado
     * 4. Atualiza saldos das carteiras (se sucesso)
     * 5. Atualiza status da transação
     * 
     * @authenticated
     * 
     * @bodyParam amount float required Valor do pagamento (mínimo 0.01). Example: 100.50
     * @bodyParam gateway_name string required Nome do gateway. Deve ser: subadquirente_a ou subadquirente_b. Example: subadquirente_a
     * @bodyParam payment_token string required Token de pagamento (simulado). Example: tok_123456789
     * @bodyParam payee_id integer required ID do usuário recebedor. Example: 2
     * 
     * @response 201 {
     *   "message": "Pagamento processado com sucesso.",
     *   "transaction_id": 1,
     *   "status": "completed"
     * }
     * @response 422 {
     *   "message": "Falha ao processar o pagamento.",
     *   "error": "Falha no gateway de pagamento: declined"
     * }
     * @response 422 {
     *   "message": "Falha ao processar o pagamento.",
     *   "error": "Saldo insuficiente."
     * }
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $payer = $request->user();

            if (! $payer instanceof User) {
                throw new Exception('Usuário autenticado inválido.');
            }

            $payee = User::findOrFail($request->validated('payee_id'));

            $transaction = $this->transactionService->createPayment(
                payer: $payer,
                amount: (float) $request->validated('amount'),
                gatewayName: $request->validated('gateway_name'),
                paymentToken: $request->validated('payment_token'),
                payee: $payee,
            );

            return response()->json([
                'message' => 'Pagamento processado com sucesso.',
                'transaction_id' => $transaction->getKey(),
                'status' => $transaction->status->value,
            ], 201);
        } catch (Exception $exception) {
            Log::error('Falha no processamento do pagamento: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Falha ao processar o pagamento.',
                'error' => $exception->getMessage(),
            ], 422);
        }
    }
}
