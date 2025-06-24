<?php

namespace Tests\Feature\Commands;

use App\Models\PriceList;
use App\Models\PriceListStatus;
use App\Models\Product;
use App\Models\PromoYellowPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class DisableNotActivePromoYellowPricesCommandTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testDisable()
    {
        /** @var PromoYellowPrice $promo */
        $promo = factory(PromoYellowPrice::class)->create([
            'is_enabled' => true,
            'start_at' => now()->subWeek(),
            'end_at' => now()->subMinute()
        ]);

        $this->artisan('yellow-prices:disable-not-active');
        $this->assertDatabaseHas('promo_yellow_prices', [
            'uuid' => $promo->uuid,
            'is_enabled' => false
        ]);
    }
}
