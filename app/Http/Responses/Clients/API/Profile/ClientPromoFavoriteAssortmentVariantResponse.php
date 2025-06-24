<?php

namespace App\Http\Responses\Clients\API\Profile;

use App\Http\Resources\Clients\API\Profile\ClientPromoFavoriteAssortmentVariantResource;
use App\Models\ClientPromoFavoriteAssortmentVariant;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ClientPromoFavoriteAssortmentVariantResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ClientPromoFavoriteAssortmentVariantResource::class;

    /**
     * @var string
     */
    protected $model = ClientPromoFavoriteAssortmentVariant::class;

    /**
     * @var array
     */
    protected $attributes = [
        'client_uuid',
        'can_be_activated_till',
        'created_at',
        'updated_at',
    ];
}
