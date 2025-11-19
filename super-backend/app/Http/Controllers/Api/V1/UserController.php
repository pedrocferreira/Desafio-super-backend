<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Autenticação
 * 
 * Endpoints relacionados ao usuário autenticado.
 */
class UserController extends Controller
{
    /**
     * Obter Usuário Autenticado
     * 
     * Retorna os dados do usuário atualmente autenticado.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "id": 1,
     *   "name": "Administrador",
     *   "email": "admin@admin.com",
     *   "email_verified_at": "2025-11-17T19:00:00.000000Z",
     *   "created_at": "2025-11-17T19:00:00.000000Z",
     *   "updated_at": "2025-11-17T19:00:00.000000Z"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
