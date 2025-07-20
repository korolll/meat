<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\ProductService;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Documentation",
 *     description="Your API description"
 * )
 */
class ProductController extends Controller
{
    protected $productService;
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Товары"},
     *     summary="Список товаров",
     *     description="Авторизация не требуется",
     *     @OA\Response(
     *         response=200,
     *         description="Список товаров",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Говядина"),
     *             @OA\Property(property="description", type="string", example="Говядина"),
     *             @OA\Property(property="price", type="number", example=500),
     *             @OA\Property(property="category", type="string", example="Мясо"),
     *             @OA\Property(property="in_stock", type="boolean", example=true)
     *         ))
     *     )
     * )
     */
    public function index()
    {
        $products = $this->productService->getAllProducts();
        return response($products);
    }
}
