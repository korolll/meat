<?php

namespace App\Http\Responses\Clients\API;

use App\Services\Framework\Http\CollectionRequest;

class StoreAssortmentCollectionResponse extends AssortmentCollectionResponse
{
    /**
     * @param \App\Services\Framework\Http\CollectionRequest $request
     * @param                                                $query
     *
     * @throws \App\Exceptions\TealsyException
     */
    public function __construct(CollectionRequest $request, $query)
    {
        parent::__construct($request, $query);
        $this->attributes[] = 'current_price';
        $this->attributes[] = 'has_yellow_price';
        $this->attributeMappings['current_price'] = 'product.price';
    }


    /**
     * @param string $operator
     * @param        $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereHasYellowPrice(string $operator, $value)
    {
        if ($value) {
            return $this->query->whereNotNull('promo_yellow.assortment_uuid');
        } else {
            return $this->query->whereNull('promo_yellow.assortment_uuid');
        }
    }
}
