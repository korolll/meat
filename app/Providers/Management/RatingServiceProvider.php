<?php

namespace App\Providers\Management;

use App\Services\Management\Rating\OrderProductRatingScoreFactory;
use App\Services\Management\Rating\RatingFactory;
use App\Services\Management\Rating\RatingFactoryContract;
use App\Services\Management\Rating\RatingScoreFactory;
use App\Services\Management\Rating\RatingScoreFactoryContract;
use App\Services\Management\Rating\ReceiptLineRatingScoreFactory;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RatingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        'factory.rating-score.receipt-line' => ReceiptLineRatingScoreFactory::class,
        'factory.rating-score.order-product' => OrderProductRatingScoreFactory::class,
        RatingFactoryContract::class => RatingFactory::class,
        RatingScoreFactoryContract::class => RatingScoreFactory::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
