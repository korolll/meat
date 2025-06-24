<?php

namespace App\Services\Management\Client\Product\Discount;

use App\Models\PromoDescription;

interface PromoDescriptionResolverInterface
{
    /**
     * @param string $discountType
     *
     * @return \App\Models\PromoDescription|null
     */
    public function resolve(string $discountType): ?PromoDescription;
}
