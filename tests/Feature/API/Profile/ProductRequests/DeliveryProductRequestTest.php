<?php

namespace Tests\Feature\API\Profile\ProductRequests;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductRequestDeliveryStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class DeliveryProductRequestTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $request = factory(ProductRequest::class)->create();

        $self = $request->deliveryUser;
        $json = $this->be($self)->getJson('/api/profile/product-requests/delivery');

        $json->assertJsonStructure([
            'data' => [
                ['uuid', 'confirmed_date']
            ]
        ]);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $request->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $request = factory(ProductRequest::class)->state('waiting-for-delivery')->create();

        $self = factory(User::class)->state('delivery-service')->create();
        $json = $this->be($self)->postJson("/api/profile/product-requests/delivery", [
            'product_request_uuid' => $request->uuid,
            'delivery_comment' => 'test_comment'
        ]);

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $request->uuid,
            ],
        ]);

        $this->assertDatabaseHas('product_requests', [
            'uuid' => $request->uuid,
            'delivery_user_uuid' => $self->uuid,
            'product_request_delivery_status_id' => ProductRequestDeliveryStatus::ID_IN_WORK,
            'delivery_comment' => 'test_comment'
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $request = factory(ProductRequest::class)->create();

        $self = $request->deliveryUser;
        $json = $this->be($self)->getJson("/api/profile/product-requests/delivery/{$request->uuid}");

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
        $request = factory(ProductRequest::class)->create();
        $product = factory(Product::class)->create(['user_uuid' => $request->delivery_user_uuid]);

        $quantity = 19;
        $request->products()->attach($product, [
            'quantity' => $quantity,
            'quantity_actual' => $quantity,
            'price' => $product->price,
        ]);

        $self = $request->deliveryUser;
        $json = $this->be($self)->getJson("/api/profile/product-requests/delivery/{$request->uuid}/products");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'product_uuid' => $product->uuid,
                ],
            ],
        ]);
    }
}
