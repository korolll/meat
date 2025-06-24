<?php

namespace Tests\Feature\Commands;

use App\Models\PriceList;
use App\Models\PriceListStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class PriceListRotateCommandTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testRotate()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Product $product */
        $product = factory(Product::class)->create([
            'price' => 100
        ]);
        /** @var PriceList $priceListCurrent */
        $priceListCurrent = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'customer_user_uuid' => null,
            'date_till' => now()->addDay(),
            'price_list_status_id' => PriceListStatus::CURRENT
        ]);
        $priceListCurrent->products()->attach([$product->uuid => ['price_new' => 100]]);

        /** @var PriceList $priceListOld */
        $priceListOld = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'customer_user_uuid' => null,
            'date_till' => now()->subWeek(),
            'price_list_status_id' => PriceListStatus::ARCHIVE
        ]);
        $priceListOld->products()->attach([$product->uuid => ['price_new' => 1]]);

        /** @var PriceList $priceListNew */
        $priceListNew = factory(PriceList::class)->create([
            'user_uuid' => $user->uuid,
            'customer_user_uuid' => null,
            'date_from' => now()->subMinute(),
            'price_list_status_id' => PriceListStatus::FUTURE
        ]);
        $priceListNew->products()->attach([$product->uuid => ['price_new' => 9]]);

        $this->artisan('price-list:rotate');

        $this->assertDatabaseHas('products', [
            'uuid' => $product->uuid,
            'price' => 9
        ]);
        $this->assertDatabaseHas('price_lists', [
            'uuid' => $priceListNew->uuid,
            'price_list_status_id' => PriceListStatus::CURRENT
        ]);
        $this->assertDatabaseHas('price_lists', [
            'uuid' => $priceListCurrent->uuid,
            'price_list_status_id' => PriceListStatus::ARCHIVE
        ]);
        $this->assertDatabaseMissing('price_lists', [
            'uuid' => $priceListOld->uuid
        ]);
    }
}
