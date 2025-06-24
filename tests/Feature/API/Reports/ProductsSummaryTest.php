<?php

namespace Tests\Feature\API\Reports;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCaseNotificationsFake;

class ProductsSummaryTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $product = factory(Product::class)->create([
            'user_uuid' => factory(User::class)->state('store')->create()->uuid,
        ]);

        $this->createTransaction($product, 0, +5, 5);
        $this->createTransaction($product, 5, -2, 3);

        $data = [
            'date_start' => now()->subDay(),
            'date_end' => now()->addMinute(),
            'order_by' => [
                'catalog_name' => 'desc',
                'delta_plus' => 'desc',
            ],
            'where' => [
                ['assortment_name', 'LIKE', '%' . $product->assortment->name . '%'],
            ],
            'per_page' => 1,
        ];

        $self = $product->user;
        $json = $this->be($self)->json('get', '/api/reports/products-summary', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $product->uuid,
                    'delta_minus' => -2,
                    'delta_plus' => 5,
                    'quantity_on_start' => 0,
                    'quantity_on_end' => 3,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $product = factory(Product::class)->create([
            'user_uuid' => factory(User::class)->state('store')->create()->uuid,
        ]);

        $uuidOne = $this->createTransaction($product, 0, +5, 5);
        $uuidTwo = $this->createTransaction($product, 5, -2, 3);

        $data = [
            'date_start' => now()->subDay(),
            'date_end' => now()->addMinute(),
            'order_by' => [
                'created_at' => 'desc',
            ],
        ];

        $self = $product->user;
        $json = $this->be($self)->json('get', "/api/reports/products-summary/{$product->uuid}", $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $uuidTwo,
                    'quantity_delta' => -2,
                ],
                [
                    'uuid' => $uuidOne,
                    'quantity_delta' => 5,
                ],
            ],
        ]);
    }

    /**
     * @param Product $product
     * @param int $quantityOld
     * @param int $quantityDelta
     * @param int $quantityNew
     * @return string
     */
    private function createTransaction(Product $product, $quantityOld, $quantityDelta, $quantityNew)
    {
        $uuid = Str::orderedUuid()->toString();

        DB::table('warehouse_transactions')->insert([
            'uuid' => $uuid,
            'product_uuid' => $product->uuid,
            'quantity_old' => $quantityOld,
            'quantity_delta' => $quantityDelta,
            'quantity_new' => $quantityNew,
            'reference_type' => 'dummy',
            'reference_id' => $product->uuid,
            'created_at' => now(),
        ]);

        return $uuid;
    }
}
