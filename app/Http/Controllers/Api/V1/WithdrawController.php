<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWithdrawRequest;
use App\Models\Withdraw;
use App\Services\WithdrawService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    public function __construct(private readonly WithdrawService $withdrawService)
    {
    }

    /**
     * @group Saques
     * @authenticated
     *
     * Lista os saques solicitados pelo usuário autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $withdraws = $request->user()
            ->withdraws()
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($withdraws);
    }

    /**
     * @group Saques
     * @authenticated
     *
     * Solicita um novo saque utilizando a subadquirente configurada.
     */
    public function store(StoreWithdrawRequest $request): JsonResponse
    {
        $withdraw = $this->withdrawService->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Saque solicitado com sucesso.',
            'data' => $withdraw,
        ], 201);
    }

    /**
     * @group Saques
     * @authenticated
     *
     * Retorna os detalhes de um saque específico do usuário autenticado.
     */
    public function show(Request $request, int|string $withdraw): JsonResponse
    {
        $withdrawModel = $request->user()
            ->withdraws()
            ->find($withdraw);

        if (! $withdrawModel) {
            return response()->json([
                'message' => 'Saque não encontrado.',
            ], 404);
        }

        return response()->json($withdrawModel);
    }
}

