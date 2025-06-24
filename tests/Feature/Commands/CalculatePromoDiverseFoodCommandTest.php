<?php


namespace Tests\Feature\Commands;

use App\Models\PromoDiverseFoodClientStat;
use App\Models\PromoDiverseFoodSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class CalculatePromoDiverseFoodCommandTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testExecute()
    {
        /** @var PromoDiverseFoodSettings $setting */
        $setting = factory(PromoDiverseFoodSettings::class)->create([
            'count_purchases' => 2,
            'count_rating_scores' => 1,
            'discount_percent' => 5.5,
            'is_enabled' => true,
        ]);

        /** @var PromoDiverseFoodClientStat $stat */
        $stat = PromoDiverseFoodClientStat::factory()->createOne([
            'month' => now()->startOfMonth()->subDay()->format('Y-m'),
            'purchased_count' => 10,
            'rated_count' => 10,
        ]);

        $this->artisan('promo-diverse-food:calculate');
        $this->assertDatabaseHas('promo_diverse_food_client_discounts', [
            'client_uuid' => $stat->client_uuid,
            'discount_percent' => $setting->discount_percent,
            'start_at' => now()->startOfMonth(),
            'end_at' => now()->endOfMonth()
        ]);
    }

//    /**
//     * @test
//     */
//    public function discountExists()
//    {
//        $lastMonth = now()->startOfMonth()->subMonth();
//
//        /** @var PromoDiverseFoodSettings $setting */
//        $setting = factory(PromoDiverseFoodSettings::class)->create([
//            'count_purchases' => 2,
//            'count_rating_scores' => 1,
//            'discount_percent' => 5.5,
//            'is_enabled' => true,
//        ]);
//
//        /** @var Receipt $receipt */
//        $receipt = factory(Receipt::class)->create([
//            'created_at' => $lastMonth
//        ]);
//        $rLines = factory(ReceiptLine::class)->times(3)->make();
//        $receipt->receiptLines()->saveMany($rLines);
//        $line = $rLines->first();
//        factory(RatingScore::class)->create([
//            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
//            'rated_reference_id' => $line->assortment_uuid,
//            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
//            'rated_by_reference_id' => $receipt->loyaltyCard->client->uuid,
//            'rated_through_reference_type' => ReceiptLine::MORPH_TYPE_ALIAS,
//            'rated_through_reference_id' => $line->uuid,
//            'created_at' => $lastMonth
//        ]);
//        $this->artisan('promo-diverse-food:calculate');
//        $this->assertDatabaseHas('promo_diverse_food_client_discounts', [
//            'client_uuid' => $receipt->loyaltyCard->client->uuid,
//            'discount_percent' => $setting->discount_percent,
//            'end_at' => now()->endOfMonth()
//        ]);
//
//    }
//
//    /**
//     * @test
//     */
//    public function testExcludedCatalog()
//    {
//        $lastMonth = now()->startOfMonth()->subMonth();
//
//        /** @var PromoDiverseFoodSettings $setting */
//        $setting = factory(PromoDiverseFoodSettings::class)->create([
//            'count_purchases' => 3,
//            'count_rating_scores' => 1,
//            'discount_percent' => 5.5,
//            'is_enabled' => true,
//        ]);
//
//        /** @var Receipt $receipt */
//        $receipt = factory(Receipt::class)->create([
//            'created_at' => $lastMonth
//        ]);
//        $rLines = factory(ReceiptLine::class)->times(2)->make();
//        $receipt->receiptLines()->saveMany($rLines);
//        $line = $rLines->first();
//        factory(RatingScore::class)->create([
//            'rated_reference_type' => Assortment::MORPH_TYPE_ALIAS,
//            'rated_reference_id' => $line->assortment_uuid,
//            'rated_by_reference_type' => Client::MORPH_TYPE_ALIAS,
//            'rated_by_reference_id' => $receipt->loyaltyCard->client->uuid,
//            'rated_through_reference_type' => ReceiptLine::MORPH_TYPE_ALIAS,
//            'rated_through_reference_id' => $line->uuid,
//            'created_at' => $lastMonth
//        ]);
//        // У нас есть 2 покупки и одна оценка, сделаем еще одну покупку и запретим каталог с товаром этой покупки
//        /** @var ReceiptLine $rLine */
//        $rLine = factory(ReceiptLine::class)->make();
//        $receipt->receiptLines()->save($rLine);
//
//        // с учетом
//        Config::set('app.catalogs.promo.diverse_food.excluded_uuid', $rLine->assortment->catalog_uuid);
//
//        $this->artisan('promo-diverse-food:calculate');
//        $this->assertDatabaseMissing('promo_diverse_food_client_discounts', [
//            'client_uuid' => $receipt->loyaltyCard->client->uuid,
//        ]);
//
//        // без учета
//        Config::set('app.catalogs.promo.diverse_food.excluded_uuid', '');
//
//        $this->artisan('promo-diverse-food:calculate');
//        $this->assertDatabaseHas('promo_diverse_food_client_discounts', [
//            'client_uuid' => $receipt->loyaltyCard->client->uuid,
//            'discount_percent' => $setting->discount_percent,
//            'end_at' => now()->endOfMonth()
//        ]);
//    }
//
//    /**
//     * @test
//     */
//    public function discountNotExists()
//    {
//        $lastMonth = now()->startOfMonth()->subMonth();
//
//        /** @var PromoDiverseFoodSettings $setting */
//        $setting = factory(PromoDiverseFoodSettings::class)->create([
//            'count_purchases' => 2,
//            'count_rating_scores' => 1,
//            'discount_percent' => 5.5,
//            'is_enabled' => true,
//        ]);
//
//        /** @var Receipt $receipt */
//        $receipt = factory(Receipt::class)->create([
//            'created_at' => $lastMonth
//        ]);
//        $rLines = factory(ReceiptLine::class)->times(3)->make();
//        $receipt->receiptLines()->saveMany($rLines);
//
//        $this->artisan('promo-diverse-food:calculate');
//
//        $this->assertDatabaseMissing('promo_diverse_food_client_discounts', [
//            'client_uuid' => $receipt->loyaltyCard->client->uuid,
//        ]);
//    }
}
