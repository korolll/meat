<?php

namespace Tests\Feature\API\Profile;

use App\Models\Assortment;
use App\Models\Product;
use App\Models\Stocktaking;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class StocktakingTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $stocktaking = factory(Stocktaking::class)->create();

        $self = $stocktaking->user;
        $json = $this->be($self)->getJson('/api/profile/stocktakings');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $stocktaking->uuid,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        $stocktaking = factory(Stocktaking::class)->create();
        $assortment = factory(Assortment::class)->create();
        $product = factory(Product::class)->create([
            'assortment_uuid' => $assortment->uuid,
            'user_uuid' => $stocktaking->user_uuid,
        ]);

        $self = $stocktaking->user;
        $json = $this->be($self)->postJson('/api/profile/stocktakings', [
            'catalogs' => [
                ['uuid' => $assortment->catalog_uuid],
            ],
        ]);

        $json->assertSuccessful()->assertJsonStructure([
            'data' => [
                'uuid',
            ],
        ]);

        $this->assertDatabaseHas('stocktakings', [
            'user_uuid' => $self->uuid,
        ]);

        $this->assertDatabaseHas('product_stocktaking', [
            'product_uuid' => $product->uuid,
        ]);
    }

    /**
     * @param int $quantityOld
     * @param int $quantityDelta
     * @param int $quantityNew
     *
     * @test
     * @dataProvider approveProvider
     */
    public function approve($quantityOld, $quantityDelta, $quantityNew)
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Stocktaking $oldStocktaking */
        $oldStocktaking = factory(Stocktaking::class)->create([
            'created_at' => now()->subMinute(),
            'approved_at' => now()->subMinute(),
            'user_uuid' => $user->uuid
        ]);
        /** @var Stocktaking $oldStocktakingShouldBeRemoved */
        $oldStocktakingShouldBeRemoved = factory(Stocktaking::class)->create([
            'created_at' => now()->subMinutes(2),
            'approved_at' => now()->subMinutes(2),
            'user_uuid' => $user->uuid
        ]);
        /** @var Stocktaking $stocktaking */
        $stocktaking = factory(Stocktaking::class)->create([
            'user_uuid' => $user->uuid
        ]);

        /** @var Product $product */
        $product = factory(Product::class)->create([
            'user_uuid' => $stocktaking->user_uuid,
            'quantity' => $quantityOld,
        ]);

        $oldStocktaking->products()->attach($product, [
            'quantity_old' => 0,
            'quantity_new' => 1,
        ]);
        $oldStocktakingShouldBeRemoved->products()->attach($product, [
            'quantity_old' => 1,
            'quantity_new' => 0,
        ]);
        $stocktaking->products()->attach($product, [
            'quantity_old' => $quantityOld,
            'quantity_new' => $quantityNew,
        ]);

        $self = $stocktaking->user;
        $json = $this->be($self)->putJson("/api/profile/stocktakings/{$stocktaking->uuid}/approve");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $stocktaking->uuid,
            ],
        ]);

        $this->assertDatabaseHas('product_stocktaking', [
            'stocktaking_uuid' => $stocktaking->uuid,
            'product_uuid' => $product->uuid,
            'quantity_old' => $quantityOld,
            'quantity_new' => $quantityNew,
        ]);
        $this->assertDatabaseHas('product_stocktaking', [
            'stocktaking_uuid' => $oldStocktaking->uuid,
            'product_uuid' => $product->uuid,
            'quantity_old' => 0,
            'quantity_new' => 1,
        ]);
        $this->assertDatabaseMissing('stocktakings', [
            'uuid' => $oldStocktakingShouldBeRemoved->uuid,
        ]);

        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $quantityOld,
            'quantity_delta' => $quantityDelta,
            'quantity_new' => $quantityNew,
        ]);

        $this->assertDatabaseHas('products', [
            'uuid' => $product->uuid,
            'quantity' => $quantityNew,
        ]);
    }

    /**
     * Данные: [quantityOld, quantityDelta, quantityNew]
     * Расчет: quantityNew = quantityOld + quantityDelta
     *
     * @return array
     */
    public function approveProvider()
    {
        return [
            [500, 1000, 1500],
            [1000, -500, 500],
        ];
    }
}
