<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\SubadquirenteType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * @group Autenticação
 * 
 * Endpoints para autenticação de usuários usando Laravel Sanctum.
 */
class AuthController extends Controller
{
    /**
     * Registrar Novo Usuário
     * 
     * Cria um novo usuário no sistema e retorna um token de autenticação.
     * Uma carteira com saldo inicial de R$ 1.000,00 é criada automaticamente.
     * 
     * @bodyParam name string required Nome completo do usuário. Example: João Silva
     * @bodyParam email string required Email único do usuário. Example: joao@example.com
     * @bodyParam password string required Senha do usuário (mínimo 8 caracteres). Example: senha123
     * @bodyParam password_confirmation string required Confirmação da senha. Example: senha123
     * 
     * @response 201 {
     *   "message": "Usuário registrado com sucesso.",
     *   "token": "1|abc123def456...",
     *   "user": {
     *     "id": 1,
     *     "name": "João Silva",
     *     "email": "joao@example.com"
     *   }
     * }
     * @response 422 {
     *   "message": "The email has already been taken.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->wallet()->create(['balance' => 1000.00]);

        $user->subadquirentes()->create([
            'subadquirente' => SubadquirenteType::SUBADQ_A->value,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário registrado com sucesso.',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    /**
     * Login
     * 
     * Autentica um usuário e retorna um token de acesso.
     * Use este token no header `Authorization: Bearer {token}` para acessar endpoints protegidos.
     * 
     * @bodyParam email string required Email do usuário. Example: admin@admin.com
     * @bodyParam password string required Senha do usuário. Example: admin1234
     * 
     * @response 200 {
     *   "message": "Login realizado com sucesso.",
     *   "token": "2|xyz789...",
     *   "user": {
     *     "id": 1,
     *     "name": "Administrador",
     *     "email": "admin@admin.com"
     *   }
     * }
     * @response 401 {
     *   "message": "Credenciais inválidas."
     * }
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Logout
     * 
     * Revoga o token de autenticação atual do usuário.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "message": "Logout realizado com sucesso."
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }
}
