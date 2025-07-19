<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_products()
    {
        Product::factory()->create(['name' => 'Тестовый продукт', 'price' => 100, 'category' => 'Мясо', 'in_stock' => true]);
        $response = $this->getJson('/api/products');
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Тестовый продукт']);
    }
}
