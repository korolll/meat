<?php

namespace App\Services\Management\Rating;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\RatingScore;
use App\Models\ReceiptLine;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReceiptLineRatingScoreFactory extends RatingScoreFactory
{
    /**
     * @param Assortment $rated
     * @param Client $ratedBy
     * @param ReceiptLine $ratedThrough
     * @param int $value
     * @param array $additionalAttributes
     * @return RatingScore
     * @throws \Throwable
     */
    public function create($rated, $ratedBy, $ratedThrough, int $value, array $additionalAttributes = []): RatingScore
    {
//        if ($this->isRatedThisMonthBy($ratedBy, $rated)) {
//            throw new BadRequestHttpException('Assortment is already rated this month');
//        }

        $additionalAttributes = array_merge($additionalAttributes, [
            'weight' => $this->calculateWeight($ratedBy, $rated),
        ]);

        return parent::create($rated, $ratedBy, $ratedThrough, $value, $additionalAttributes);
    }

    /**
     * @param Client $client
     * @param Assortment $assortment
     * @return int
     */
    protected function calculateWeight(Client $client, Assortment $assortment): int
    {
        $salesCount = $client->receipts()->whereHas('receiptLines', function (Builder $query) use ($assortment) {
            $query->where('assortment_uuid', $assortment->uuid);
        })->limit(3)->count();

        return $salesCount >= 2 ? 3 : 1;
    }

    /**
     * @param \App\Models\Client     $client
     * @param \App\Models\Assortment $assortment
     *
     * @return void
     */
    protected function isRatedThisMonthBy(Client $client, Assortment $assortment): bool
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        return $client->ratingScoresBy()
            ->hasRatedType(Assortment::MORPH_TYPE_ALIAS)
            ->where('rated_reference_id', $assortment->uuid)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->exists();
    }
}
