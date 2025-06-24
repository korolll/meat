<?php

namespace App\Services\Management\Promos\FavoriteAssortment\Resolver;

use Carbon\CarbonInterface;

interface FavoriteAssortmentVariantResolverInterface
{
    public function resolve(?CarbonInterface $moment = null, array $clientUuids = [], bool $force = false): void;
}
