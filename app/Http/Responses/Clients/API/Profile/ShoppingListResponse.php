<?php

namespace App\Http\Responses\Clients\API\Profile;

use App\Http\Resources\Clients\API\Profile\ShoppingListResource;
use App\Models\ClientShoppingList;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ShoppingListResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ShoppingListResource::class;

    /**
     * @var string
     */
    protected $model = ClientShoppingList::class;
}
