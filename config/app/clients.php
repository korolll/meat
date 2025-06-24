<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Типы карт по которым можно делать авто-привязку к клиенту
    |--------------------------------------------------------------------------
    |
    |
    */
    'loyalty_card_types_for_generating' => env_array('APP_CLIENTS_LOYALTY_CARD_TYPES_FOR_GENERATING'),

    'max_cart_size' => env('APP_CLIENT_MAX_CART_SIZE', 1000),

    'bonuses_for_filled_profile' => (int)env('APP_CLIENT_BONUSES_FOR_FILLED_PROFILE', 20),

    'discount' => [
        'resolvers' => [
            App\Services\Management\Client\Product\Discount\Concrete\FrontolInMemoryDiscount::class,
            App\Services\Management\Client\Product\Discount\Concrete\YellowPriceDiscountResolver::class,
            App\Services\Management\Client\Product\Discount\Concrete\DiverseFoodPriceDiscountResolver::class,
            App\Services\Management\Client\Product\Discount\Concrete\FavoriteAssortmentDiscountResolver::class,
            App\Services\Management\Client\Product\Discount\Concrete\InTheShopDiscountResolver::class,
            App\Services\Management\Client\Product\Discount\Concrete\FirstOrderDiscountResolver::class,
            App\Services\Management\Client\Product\Discount\Concrete\PromocodeDiscountResolver::class,
        ]
    ]
];
