<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\JsonResponse;
use App\Services\AuthService;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Для доступа к защищенным эндпоинтам необходимо передать JWT токен в заголовке Authorization: Bearer <token>"
 * )
 */
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Регистрация"},
     *     summary="Регистрация",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone","name","address","password"},
     *             @OA\Property(property="phone", type="string", example="+79123456789"),
     *             @OA\Property(property="name", type="string", example="Иван Иванов"),
     *             @OA\Property(property="address", type="string", example="ул. Пушкина, д. 1"),
     *             @OA\Property(property="password", type="string", example="password"),
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Пользователь зарегистрирован"),
     *     @OA\Response(response=400, description="Ошибка валидации")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->authService->register($data);
        return response()->json(['message' => 'Пользователь зарегистрирован'], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Авторизация"},
     *     summary="Авторизация пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone","password"},
     *             @OA\Property(property="phone", type="string", example="+79123456789"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200, 
     *         description="Успешная авторизация",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Неверные данные")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('phone', 'password');
        $token = $this->authService->login($credentials);
        if (!$token) {
            return response()->json(['error' => 'Неверные данные'], 401);
        }
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
