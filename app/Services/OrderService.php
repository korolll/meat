<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $total = 0;
            $items = [];
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                if (!$product->in_stock) {
                    throw new \Exception('Товар не в наличии: ' . $product->name);
                }
                $total += $product->price * $item['quantity'];
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price
                ];
            }
            $order = Order::create([
                'user_id' => $user->id,
                'comment' => $data['comment'] ?? null,
                'total_price' => $total,
                'status' => 'pending',
            ]);
            foreach ($items as $item) {
                $order->items()->create($item);
            }
            return $order;
        });
    }

    public function getUserOrders($user)
    {
        return Order::with('items.product')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }
}
