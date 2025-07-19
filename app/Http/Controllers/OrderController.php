<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Заказы"},
     *     summary="Оформить заказ",
     *     description="Создание заказа. Требуется авторизация.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="JWT токен в формате: Bearer {token}",
     *         @OA\Schema(type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 required={"product_id", "quantity"},
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=2)
     *             )),
     *             @OA\Property(property="comment", type="string", example="Доставить до 18:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201, 
     *         description="Заказ создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизован или неверный токен",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Ошибка валидации")
     * )
     */
    public function store(OrderRequest $request): JsonResponse
    {
        $user = Auth::user();
        try {
            $order = $this->orderService->createOrder($user, $request->validated());
            return response()->json(['order_id' => $order->id, 'status' => $order->status], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Заказы"},
     *     summary="История заказов",
     *     description="Получение списка заказов. Требуется авторизация.",
     *     operationId="getOrders",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer {token}",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjMyNTQ5MjAwLCJleHAiOjE2MzI1NTI4MDAsIm5iZiI6MTYzMjU0OTIwMCwianRpIjoiMjNkOGFiYzEyM2QxMmFiYyIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.HsQtB1bqkYYdxV9nqjqG9zJpWBmH4nqzxH3mJfU1E8Q"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cписок заказов",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="total_price", type="number", example=2500.00),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="comment", type="string", example="Доставить до 18:00"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-19T16:53:42.000000Z"),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="product_id", type="integer", example=1),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", example=1250.00),
     *                         @OA\Property(
     *                             property="product",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Говядина"),
     *                             @OA\Property(property="description", type="string", example="Говядина"),
     *                             @OA\Property(property="price", type="number", example=1250.00),
     *                             @OA\Property(property="category", type="string", example="Мясо"),
     *                             @OA\Property(property="in_stock", type="boolean", example=true)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка авторизации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Нет доступа",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $orders = $this->orderService->getUserOrders($user);
        return response()->json($orders);
    }
}
