<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePixRequest;
use App\Models\Pix;
use App\Services\PixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PixController extends Controller
{
    public function __construct(private readonly PixService $pixService)
    {
    }

    /**
     * @group PIX
     * @authenticated
     *
     * Lista os PIX gerados pelo usuário autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $pix = $request->user()
            ->pix()
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'data' => $pix->items(),
            'current_page' => $pix->currentPage(),
            'per_page' => $pix->perPage(),
            'total' => $pix->total(),
        ]);
    }

    /**
     * @group PIX
     * @authenticated
     *
     * Gera um novo PIX utilizando a subadquirente configurada.
     */
    public function store(StorePixRequest $request): JsonResponse
    {
        $pix = $this->pixService->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'PIX gerado com sucesso.',
            'data' => $pix,
        ], 201);
    }

    /**
     * @group PIX
     * @authenticated
     *
     * Retorna os detalhes de um PIX específico do usuário autenticado.
     */
    public function show(Request $request, int|string $pix): JsonResponse
    {
        $pixModel = $request->user()
            ->pix()
            ->find($pix);

        if (! $pixModel) {
            return response()->json([
                'message' => 'PIX não encontrado.',
            ], 404);
        }

        return response()->json($pixModel);
    }
}

