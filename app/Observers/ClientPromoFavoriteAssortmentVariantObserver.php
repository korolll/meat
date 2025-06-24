<?php

namespace App\Observers;

use App\Models\ClientPromoFavoriteAssortmentVariant;
use App\Services\Management\Promos\FavoriteAssortment\FavoriteAssortmentActivatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientPromoFavoriteAssortmentVariantObserver
{
    /**
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     *
     * @return void
     */
    public function created(ClientPromoFavoriteAssortmentVariant $variant)
    {
        $this->prolong($variant);
    }

    /**
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     *
     * @return void
     */
    public function updated(ClientPromoFavoriteAssortmentVariant $variant)
    {
        if ($variant->isDirty('can_be_activated_till')) {
            $this->prolong($variant);
        }
    }

    /**
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     *
     * @return void
     */
    protected function prolong(ClientPromoFavoriteAssortmentVariant $variant)
    {
        /** @var FavoriteAssortmentActivatorInterface $activator */
        $activator = app(FavoriteAssortmentActivatorInterface::class);
        try {
            $activator->prolongLast($variant);
        } catch (BadRequestHttpException $exception) {
            // ignore
        }
    }
}
