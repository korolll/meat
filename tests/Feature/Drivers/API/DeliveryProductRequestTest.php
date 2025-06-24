<?php

namespace Tests\Feature\Drivers\API;

use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class DeliveryProductRequestTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Auth::shouldUse('api-drivers');
    }

    /**
     * @test
     */
    public function show()
    {
        $request = factory(ProductRequest::class)->state('has-transportation')->create();

        $self = $request->transportation->driver;
        $json = $this->be($self)->getJson("/drivers/api/product-requests/delivery/{$request->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $request->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function products()
    {
        $request = factory(ProductRequest::class)->state('has-transportation')->create();
        $product = factory(Product::class)->create(['user_uuid' => $request->delivery_user_uuid]);

        $request->products()->attach($product, [
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $self = $request->transportation->driver;
        $json = $this->be($self)->getJson("/drivers/api/product-requests/delivery/{$request->uuid}/products");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'product_uuid' => $product->uuid,
                ],
            ],
        ]);
    }
}
