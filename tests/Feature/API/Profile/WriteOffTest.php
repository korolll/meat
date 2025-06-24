<?php

namespace Tests\Feature\API;

use App\Models\Product;
use App\Models\User;
use App\Models\WriteOff;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class WriteOffTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @param int $quantityOld
     * @param int $quantityDelta
     * @param int $quantityNew
     *
     * @test
     * @dataProvider storeProvider
     */
    public function store($quantityOld, $quantityDelta, $quantityNew)
    {
        $user = factory(User::class)->state('store')->create();

        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'quantity' => $quantityOld,
        ]);

        $writeOff = factory(WriteOff::class)->make([
            'user_uuid' => $user->uuid,
            'product_uuid' => $product->uuid,
            'quantity_delta' => $quantityDelta,
        ]);

        $self = $user;
        $json = $this->be($self)->postJson('/api/profile/write-offs', $writeOff->only([
            'product_uuid',
            'write_off_reason_id',
            'quantity_delta',
            'comment',
        ]));

        $json->assertSuccessful()->assertJson([
            'data' => [
                'product_uuid' => $product->uuid,
            ],
        ]);

        $this->assertDatabaseHas('write_offs', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $quantityOld,
            'quantity_delta' => $quantityDelta,
            'quantity_new' => $quantityNew,
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
     *
     */
    public function testStoreBatch()
    {
        /** @var User $user */
        $user = factory(User::class)->state('store')->create();
        $quantityOld = $this->faker->numberBetween(1, 5);
        $quantityNew = $this->faker->numberBetween(6, 10);
        $quantityDelta = $quantityNew - $quantityOld;

        $quantityOld2 = $this->faker->numberBetween(1, 5);
        $quantityNew2 = $this->faker->numberBetween(6, 10);
        $quantityDelta2 = $quantityNew2 - $quantityOld2;

        /** @var Product $product */
        $product = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'quantity' => $quantityOld,
        ]);
        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $user->uuid,
            'quantity' => $quantityOld2,
        ]);

        /** @var WriteOff $writeOff */
        $writeOff = factory(WriteOff::class)->make([
            'user_uuid' => $user->uuid,
            'product_uuid' => $product->uuid,
            'quantity_delta' => $quantityDelta,
        ]);
        /** @var WriteOff $writeOff2 */
        $writeOff2 = factory(WriteOff::class)->make([
            'user_uuid' => $user->uuid,
            'product_uuid' => $product2->uuid,
            'quantity_delta' => $quantityDelta2,
        ]);

        $data = $writeOff->only([
            'write_off_reason_id',
            'comment',
        ]);
        $data['products'] = [
            $writeOff->only([
                'product_uuid',
                'quantity_delta',
            ]),
            $writeOff2->only([
                'product_uuid',
                'quantity_delta',
            ]),
        ];

        $self = $user;
        $json = $this->be($self)->postJson('/api/profile/write-offs-batch', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                ['product_uuid' => $product->uuid],
                ['product_uuid' => $product2->uuid],
            ],
        ]);

        $this->assertDatabaseHas('write_offs', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $quantityOld,
            'quantity_delta' => $quantityDelta,
            'quantity_new' => $quantityNew,
        ]);
        $this->assertDatabaseHas('write_offs', [
            'product_uuid' => $product2->uuid,
            'quantity_old' => $quantityOld2,
            'quantity_delta' => $quantityDelta2,
            'quantity_new' => $quantityNew2,
        ]);

        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product->uuid,
            'quantity_old' => $quantityOld,
            'quantity_delta' => $quantityDelta,
            'quantity_new' => $quantityNew,
        ]);
        $this->assertDatabaseHas('warehouse_transactions', [
            'product_uuid' => $product2->uuid,
            'quantity_old' => $quantityOld2,
            'quantity_delta' => $quantityDelta2,
            'quantity_new' => $quantityNew2,
        ]);

        $this->assertDatabaseHas('products', [
            'uuid' => $product->uuid,
            'quantity' => $quantityNew,
        ]);
        $this->assertDatabaseHas('products', [
            'uuid' => $product2->uuid,
            'quantity' => $quantityNew2,
        ]);
    }

    /**
     * Данные: [quantityOld, quantityDelta, quantityNew]
     * Расчет: quantityNew = quantityOld + quantityDelta
     *
     * @return array
     */
    public function storeProvider()
    {
        return [
            [100, -50, 50],
        ];
    }
}
