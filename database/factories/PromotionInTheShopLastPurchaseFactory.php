<?php

namespace Database\Factories;

use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\Client;
use App\Models\PromotionInTheShopLastPurchase;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionInTheShopLastPurchaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PromotionInTheShopLastPurchase::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $lastBuyDate = $this->faker->numberBetween(1, 100);
        $deleteAfterDate = $lastBuyDate + config('app.promotions.in_the_shop.tracking_period');

        return [
            'client_uuid' => function() {
                return factory(Client::class)->create()->uuid;
            },
            'catalog_uuid' => function() {
                return factory(Catalog::class)->create()->uuid;
            },
            'created_at' => now()->subDays($lastBuyDate),
            'updated_at' => now()->subDays($lastBuyDate),
            'delete_after' => now()->subDays($deleteAfterDate),
        ];
    }

    public function stateNotBoughtLongTime(): PromotionInTheShopLastPurchaseFactory
    {
        $longTimeDelay = config('app.promotions.in_the_shop.offer_delay');
        $lastBuyDate = $this->faker->numberBetween($longTimeDelay + 1, $longTimeDelay + 100);
        $deleteAfterDate = $lastBuyDate + config('app.promotions.in_the_shop.tracking_period');

        return $this->state(function (array $attributes) use ($lastBuyDate, $deleteAfterDate) {
            return [
                'created_at' => now()->subDays($lastBuyDate),
                'updated_at' => now()->subDays($lastBuyDate),
                'delete_after' => now()->subDays($deleteAfterDate),
            ];
        });
    }

    public function stateClient(Client $client): PromotionInTheShopLastPurchaseFactory
    {
        return $this->state(function (array $attributes) use ($client) {
            return [
                'client_uuid' => $client->uuid,
            ];
        });
    }

    public function stateAssortment(Assortment $assortment): PromotionInTheShopLastPurchaseFactory
    {
        return $this->state(function (array $attributes) use ($assortment) {
            return [
                'catalog_uuid' => $assortment->catalog_uuid,
            ];
        });
    }
}
