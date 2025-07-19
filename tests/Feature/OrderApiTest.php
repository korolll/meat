<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order()
    {
        $user = User::factory()->create([
            'phone' => '79990001124',
            'email' => 'orderuser@example.com',
            'password' => bcrypt('secret123')
        ]);
        $product = Product::factory()->create(['price' => 200, 'in_stock' => true]);
        $token = auth('api')->attempt(['phone' => '79990001124', 'password' => 'secret123']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', [
                'user_id' => $user->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2]
                ],
                'comment' => 'Тестовый заказ'
            ]);
        $response->assertStatus(201)
            ->assertJsonStructure(['order_id', 'status']);
    }
}
