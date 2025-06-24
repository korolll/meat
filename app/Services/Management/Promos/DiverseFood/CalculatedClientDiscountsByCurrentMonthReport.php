<?php

namespace App\Services\Management\Promos\DiverseFood;

use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\PromoDiverseFoodSettings;
use App\Services\Management\Promos\DiverseFood\ClientStatisticReport\ReportBuilder as ClientStatisticReportBuilder;
use App\Services\Management\Promos\DiverseFood\ClientStatisticReport\ReportRow as ClientStatisticReportRow;
use Illuminate\Support\Enumerable;

/**
 * @deprecated
 */
class CalculatedClientDiscountsByCurrentMonthReport implements CalculatedClientDiscountsByCurrentMonthReportInterface
{
    /** @var Enumerable|PromoDiverseFoodSettings[]  */
    private Enumerable $promoSettings;
    /** @var Enumerable|ClientStatisticReportRow[] */
    private Enumerable $clientStatistic;

    public function __construct(ClientStatisticReportBuilder $clientStatisticReportBuilder)
    {
        $this->promoSettings = PromoDiverseFoodSettings::enabled()
            // сортируем в пользу клиента
            ->orderBy('discount_percent', 'desc')
            ->get();

        $minPurchases = null;
        $minRatingScores = null;
        foreach ($this->promoSettings as $setting) {
            if (is_null($minPurchases) || $setting->count_purchases < $minPurchases) {
                $minPurchases = $setting->count_purchases;
            }
            if (is_null($minRatingScores) || $setting->count_rating_scores < $minRatingScores) {
                $minRatingScores = $setting->count_rating_scores;
            }
        }
        if (!is_null($minPurchases)) {
            $clientStatisticReportBuilder->setMinPurchases($minPurchases);
        }
        if (!is_null($minRatingScores)) {
            $clientStatisticReportBuilder->setMinRatings($minRatingScores);
        }

        $clientStatisticReportBuilder->build();

        $this->clientStatistic = collect($clientStatisticReportBuilder->getIterator())->keyBy('client_uuid');
    }

    public function getDiscount(string $clientId): float
    {
        /** @var ClientStatisticReportRow $statistic */
        $statistic = $this->clientStatistic->get($clientId);
        if (is_null($statistic)) {
            return 0.0;
        }
        foreach ($this->promoSettings as $setting) {
            if (
                $statistic->count_purchases >= $setting->count_purchases
                && $statistic->count_rating_scores >= $setting->count_rating_scores
            ) {
                return $setting->discount_percent;
            }
        }
        return 0.0;
    }

    /**
     * @return \App\Models\PromoDiverseFoodClientDiscount[]|\Traversable|void
     */
    public function getIterator()
    {
        $now = now();
        foreach ($this->clientStatistic->keys() as $clientId) {
            $discount = $this->getDiscount($clientId);
            if ($discount === 0.0) {
                continue;
            }
            $row = new PromoDiverseFoodClientDiscount([
                'discount_percent' => $discount,
                'start_at' => $now,
                'end_at' => $now->copy()->endOfMonth()
            ]);
            $row->client_uuid = $clientId;

            yield $row;
        }
    }
}
