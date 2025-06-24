<?php

namespace App\Services\Management\Client\Product\Discount;

use App\Models\PromoDescription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class PromoDescriptionResolver implements PromoDescriptionResolverInterface
{
    /**
     * @var ?Collection<string, \App\Models\PromoDescription>
     */
    private ?Collection $byDescriptionTypeMap = null;

    /**
     * @param string $discountType
     *
     * @return \App\Models\PromoDescription|null
     */
    public function resolve(string $discountType): ?PromoDescription
    {
        if ($this->byDescriptionTypeMap === null) {
            $this->loadMap();
        }

        return Arr::get($this->byDescriptionTypeMap, $discountType);
    }

    /**
     * @return void
     */
    protected function loadMap(): void
    {
        $this->byDescriptionTypeMap = PromoDescription::query()
            ->whereNotNull('discount_type')
            ->get()
            ->keyBy('discount_type');
    }
}
