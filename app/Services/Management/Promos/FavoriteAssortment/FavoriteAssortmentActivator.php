<?php

namespace App\Services\Management\Promos\FavoriteAssortment;

use App\Models\Client;
use App\Models\ClientActivePromoFavoriteAssortment;
use App\Models\ClientPromoFavoriteAssortmentVariant;
use App\Models\PromoFavoriteAssortmentSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FavoriteAssortmentActivator implements FavoriteAssortmentActivatorInterface
{
    /**
     * @param string                                           $assortmentUuid
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     *
     * @return \App\Models\ClientActivePromoFavoriteAssortment
     * @throws \Throwable
     */
    public function activate(string $assortmentUuid, ClientPromoFavoriteAssortmentVariant $variant): ClientActivePromoFavoriteAssortment
    {
        if ($variant->can_be_activated_till->isPast()) {
            throw new BadRequestHttpException('Discount is out of date');
        }

        return DB::transaction(function() use ($assortmentUuid, $variant) {
            // Lock by client
            $client = Client::whereUuid($variant->client_uuid)
                ->lockForUpdate()
                ->first();

            $option = $this->findOptions();
            if (! $option) {
                throw new BadRequestHttpException('Options not found');
            }

            if (! $this->hasInFutureActive($variant)) {
                // It is first active
                return $this->createActive($assortmentUuid, $variant, $option, now());
            }

            if ($this->isAlreadyActivatedDiscountForAssortment($client->uuid, $assortmentUuid)) {
                throw new BadRequestHttpException('Discount is already activated for that assortment');
            }

            if ($this->isAlreadyActivatedDiscountForNextDay($client->uuid)) {
                throw new BadRequestHttpException('Discount is already activated for the next day');
            }

            $nextDayStart = now()->addDay()->startOfDay();
            $this->disableCurrentActive($variant);
            return $this->createActive($assortmentUuid, $variant, $option, $nextDayStart);
        });
    }

    /**
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     *
     * @return \App\Models\ClientActivePromoFavoriteAssortment|null
     * @throws \Throwable
     */
    public function prolongLast(ClientPromoFavoriteAssortmentVariant $variant): ?ClientActivePromoFavoriteAssortment
    {
        return DB::transaction(function() use ($variant) {
            // Lock by client
            Client::whereUuid($variant->client_uuid)
                ->lockForUpdate()
                ->first();

            $option = $this->findOptions();
            if (! $option) {
                return null;
            }

            $lastActivated = ClientActivePromoFavoriteAssortment::query()
                ->where('client_uuid', $variant->client_uuid)
                ->orderBy('active_from', 'DESC')
                ->first();

            if (! $lastActivated) {
                return null;
            }

            // Don't prolong banned assortment
            if ($lastActivated->assortment->isForbiddenForDiscount()) {
                return null;
            }

            return $this->createActive($lastActivated->assortment_uuid, $variant, $option);
        });
    }

    /**
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     *
     * @return bool
     */
    protected function hasInFutureActive(ClientPromoFavoriteAssortmentVariant $variant): bool
    {
        return ClientActivePromoFavoriteAssortment::query()
            ->where('active_to', '>=', now()->startOfDay())
            ->where('client_uuid', $variant->client_uuid)
            ->exists();
    }

    /**
     * @param string                                           $assortmentUuid
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     * @param \App\Models\PromoFavoriteAssortmentSetting       $option
     * @param \Carbon\CarbonInterface|null                     $date
     *
     * @return \App\Models\ClientActivePromoFavoriteAssortment
     */
    protected function createActive(string $assortmentUuid, ClientPromoFavoriteAssortmentVariant $variant, PromoFavoriteAssortmentSetting $option, ?CarbonInterface $date = null): ClientActivePromoFavoriteAssortment
    {
        $active = ClientActivePromoFavoriteAssortment::firstOrNew([
            'client_uuid' => $variant->client_uuid,
            'assortment_uuid' => $assortmentUuid,
        ]);

        if ($date) {
            $active->active_from = $date;
        } else {
            $date = now();
        }

        $skippedDays = $variant->updated_at->startOfDay()->diffInDays(now()->startOfDay(), true);
        $leftDays = $option->number_of_active_days - $skippedDays;
        if ($leftDays <= 0) {
            throw new BadRequestHttpException('No left days for discount');
        }

        $active->active_to = $date->addDays($leftDays)->endOfDay();
        $active->discount_percent = $option->discount_percent;
        $active->save();

        return $active;
    }

    /**
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     *
     * @return bool
     */
    protected function disableCurrentActive(ClientPromoFavoriteAssortmentVariant $variant): bool
    {
        $currentActivates = ClientActivePromoFavoriteAssortment::activeAt()
            ->where('client_uuid', $variant->client_uuid)
            ->get();

        $todayEnd = now()->endOfDay();
        /** @var ClientActivePromoFavoriteAssortment $activated */
        foreach ($currentActivates as $activated) {
            $activated->active_to = $todayEnd;
            $activated->save();
        }

        return $currentActivates->isNotEmpty();
    }

    /**
     * @return \App\Models\PromoFavoriteAssortmentSetting|null
     */
    protected function findOptions(): ?PromoFavoriteAssortmentSetting
    {
        return PromoFavoriteAssortmentSetting::enabled()
            // May be added others filter
            ->first();
    }

    /**
     * @param string $clientUuid
     *
     * @return bool
     */
    protected function isAlreadyActivatedDiscountForNextDay(string $clientUuid): bool
    {
        $now = now();
        return ClientActivePromoFavoriteAssortment::query()
            ->whereBetween('updated_at', [
                $now->startOfDay(),
                $now->endOfDay(),
            ])
            ->where('client_uuid', $clientUuid)
            ->where('active_from', '>', $now->endOfDay())
            ->exists();
    }

    /**
     * @param string $clientUuid
     * @param string $assortmentUuid
     *
     * @return bool
     */
    protected function isAlreadyActivatedDiscountForAssortment(string $clientUuid, string $assortmentUuid): bool
    {
        $now = now();
        return ClientActivePromoFavoriteAssortment::activeAt($now)
            ->where('client_uuid', $clientUuid)
            ->where('assortment_uuid', $assortmentUuid)
            ->exists();
    }
}
