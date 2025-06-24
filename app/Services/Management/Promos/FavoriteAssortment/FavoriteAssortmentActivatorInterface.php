<?php

namespace App\Services\Management\Promos\FavoriteAssortment;

use App\Models\ClientActivePromoFavoriteAssortment;
use App\Models\ClientPromoFavoriteAssortmentVariant;

interface FavoriteAssortmentActivatorInterface
{
    public function activate(string $assortmentUuid, ClientPromoFavoriteAssortmentVariant $variant): ClientActivePromoFavoriteAssortment;

    public function prolongLast(ClientPromoFavoriteAssortmentVariant $variant): ?ClientActivePromoFavoriteAssortment;
}
