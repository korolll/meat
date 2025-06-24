<?php

namespace App\Http\Responses\Clients\API\Profile;

use App\Http\Resources\Clients\API\Profile\ClientActivePromoFavoriteAssortmentResource;
use App\Models\ClientActivePromoFavoriteAssortment;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ClientActivePromoFavoriteAssortmentResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ClientActivePromoFavoriteAssortmentResource::class;

    /**
     * @var string
     */
    protected $model = ClientActivePromoFavoriteAssortment::class;

    /**
     * @var array
     */
    protected $attributes = [
        'client_uuid',
        'assortment_uuid',
        'assortment_name',
        'active_from',
        'active_to',
        'created_at',
        'updated_at',
    ];
}
