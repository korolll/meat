<?php


namespace App\Services\Management\Profiles\Promotions;


use App\Exceptions\ClientExceptions\PromotionInTheShopActivatedException;
use App\Models\Client;
use App\Models\ClientPromotion;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Enumerable;

class InTheShopService implements InTheShopServiceContract
{
    public const PROMOTION_KEY = 'IN_THE_SHOP';
    public const ASSORTMENT_MARK_NEW = 'new';
    public const ASSORTMENT_MARK_SALE = 'sale';
    public const ASSORTMENT_MARK_NOT_BOUGHT_LONGTIME = 'not_bought_longtime';

    /**
     * @var \App\Services\Management\Profiles\Promotions\InTheShopAssortmentFinderContract
     */
    private InTheShopAssortmentFinderContract $finder;

    /**
     * @var float
     */
    private float $discount;

    /**
     * @param \App\Services\Management\Profiles\Promotions\InTheShopAssortmentFinderContract $finder
     * @param float                                                                          $discount
     */
    public function __construct(InTheShopAssortmentFinderContract $finder, float $discount)
    {
        $this->finder = $finder;
        $this->discount = $discount;
    }

    /**
     * @param \App\Models\Client $client
     * @param \App\Models\User   $user
     *
     * @throws \App\Exceptions\ClientExceptions\PromotionInTheShopActivatedException
     * @throws \Throwable
     */
    public function activate(Client $client, User $user): void
    {
        if ($this->isActivated($client, $user)) {
            throw new PromotionInTheShopActivatedException();
        }

        $assortmentMarkedNew = $this->finder->findAssortmentMarkedNew($user);
        $assortmentMarkedSale = $this->finder->findAssortmentMarkedSale($user);
        $assortmentNotBoughtLongTime = $this->finder->findAssortmentNotBoughtLongTime($client, $user);

        DB::transaction(function () use ($client, $user, $assortmentMarkedNew, $assortmentMarkedSale, $assortmentNotBoughtLongTime) {
            /** @var ClientPromotion $promotion */
            $promotion = $client->clientPromotions()
                ->make([
                    'promotion_type' => static::PROMOTION_KEY,
                    'started_at' => Carbon::today(),
                    'expired_at' => Carbon::now()->endOfDay(),
                    'discount_percent' => $this->discount,
                ]);

            $promotion->shop()->associate($user);
            $promotion->client()->associate($client);
            $promotion->save();
            $assortments = array_merge(
                $this->formatAssortmentForInsert($promotion->uuid, $assortmentNotBoughtLongTime, self::ASSORTMENT_MARK_NOT_BOUGHT_LONGTIME),
                $this->formatAssortmentForInsert($promotion->uuid, $assortmentMarkedNew, self::ASSORTMENT_MARK_NEW),
                $this->formatAssortmentForInsert($promotion->uuid, $assortmentMarkedSale, self::ASSORTMENT_MARK_SALE),
            );

            $assortments = array_slice($assortments, 0, 6);
            foreach ($assortments as $assortment) {
                $promotion->promotionInTheShopAssortments()
                    ->create($assortment)
                    ->save();
            }
        });
    }

    /**
     * @param \App\Models\Client $client
     * @param \App\Models\User   $user
     *
     * @return \App\Models\ClientPromotion|null
     */
    public function getActivated(Client $client, User $user): ?ClientPromotion
    {
        return $client->clientPromotions()
            ->where('user_uuid', $user->uuid)
            ->where('promotion_type', '=', static::PROMOTION_KEY)
            ->whereRaw(DB::raw('NOW() BETWEEN started_at AND expired_at'))
            ->limit(1)
            ->get()
            ->first();
    }

    /**
     * @param \App\Models\Client $client
     * @param \App\Models\User   $user
     *
     * @return bool
     */
    private function isActivated(Client $client, User $user): bool
    {
        return $client
            ->clientPromotions()
            ->where('user_uuid', $user->uuid)
            ->where('promotion_type', static::PROMOTION_KEY)
            ->whereRaw(DB::raw('NOW() between started_at AND expired_at'))
            ->exists();
    }

    /**
     * @param string                         $promotionUuid
     * @param \Illuminate\Support\Enumerable $products
     * @param string                         $mark
     *
     * @return array
     */
    private function formatAssortmentForInsert(string $promotionUuid, Enumerable $products, string $mark): array
    {
        $result = [];

        foreach ($products as $product) {
            $result[] = [
                'client_promotion_uuid' => $promotionUuid,
                'assortment_uuid' => $product->uuid,
                'assortment_mark' => $mark,
            ];
        }

        return $result;
    }
}
