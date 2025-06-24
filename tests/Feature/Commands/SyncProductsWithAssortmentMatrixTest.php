<?php

namespace Tests\Feature\Commands;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class SyncProductsWithAssortmentMatrixTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testCommand()
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();
        $user = $product->user;

        $this->artisan('assortment-matrix:sync');
        $this->assertDatabaseHas('assortment_matrices', [
            'user_uuid' => $user->uuid,
            'assortment_uuid' => $product->assortment_uuid
        ]);
    }
}
