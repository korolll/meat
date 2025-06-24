<?php

namespace App\Providers;

use App\Events;
use App\Listeners;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Events\AssortmentCreated::class => [
            Listeners\SendAssortmentCreatedNotification::class,
        ],
        Events\ReceiptReceived::class => [
            Listeners\ProduceReceiptRelatedWarehouseTransactions::class,
            Listeners\ProduceClientBonus::class,
            Listeners\UpdatePromoDiverseFoodClientStatListener::class,
            Listeners\UpdatePromoFavoriteAssortmentVariantListener::class,
            Listeners\UpdatePromoInTheShopListener::class,
        ],
        Events\UserRegistered::class => [
            Listeners\SendUserEmailConfirmationNotification::class,
            Listeners\SendUserRegisteredNotification::class,
        ],
        Events\DriverRegistered::class => [
            Listeners\SendDriverRegisteredNotification::class,
        ],
        Events\UserVerified::class => [
            Listeners\SendUserVerifiedNotification::class,
        ],
        Events\ProductRequestCreated::class => [
            Listeners\ExportProductRequestTo1C::class,
            Listeners\SendProductRequestReceivedNotification::class,
        ],
        Events\ProductRequestStatusChanged::class => [
            Listeners\ExportProductRequestTo1C::class,
            Listeners\SendProductRequestReceivedNotification::class,
        ],
        Events\CustomerProductRequestStatusChanged::class => [
            Listeners\SendCustomerProductRequestStatusOnMatchingNotification::class,
            Listeners\SendCustomerProductRequestStatusCanceledNotification::class,
        ],
        Events\SupplierProductRequestStatusChanged::class => [
            Listeners\SendSupplierProductRequestStatusDoneOrRefusedNotification::class,
        ],
        Events\RatingScoreSaved::class => [
            Listeners\CalculateRating::class,
        ],
        Events\AssortmentUpdated::class => [
            Listeners\ExportProductByAssortmentTo1C::class,
        ],
        Events\ProductReadyForExport::class => [
            Listeners\ExportProductTo1C::class,
        ],
        Events\PriceListReadyForExport1C::class => [
            Listeners\ExportPriceListTo1C::class
        ],
        Events\PublicCatalogsReadyForExport1C::class => [
            Listeners\ExportPublicCatalogsTo1C::class
        ],
        Events\PriceListReadyForExportAtol::class => [
            Listeners\ExportPriceListToAtol::class
        ],
        Events\FileUploaded::class => [
            Listeners\GenerateFileThumbnails::class,
        ],
        Events\NeedCatalogAssortmentCountUpdate::class => [
            Listeners\UpdateCatalogAssortmentCount::class,
        ],
        Events\NeedCatalogProductCountUpdate::class => [
            Listeners\UpdateCatalogProductCount::class,
        ],

        Events\OrderWithProductsCreated::class => [
            Listeners\ProduceOrderRelatedWarehouseTransactions::class
        ],
        Events\OrderStatusChanging::class => [
            Listeners\ProduceOrderRelatedWarehouseTransactions::class,
            Listeners\ProcessOrderPaymentListener::class,
            Listeners\ProduceClientBonus::class,
        ],
        Events\OrderStatusChanged::class => [
            Listeners\SendClientOrderNotification::class,
            Listeners\ProcessOrderCashFile::class,
            Listeners\ProcessOrderIIko::class,
        ],
        Events\OrderIsCreating::class => [
            Listeners\ProduceClientBonus::class,
        ],
        Events\OrderIsCreated::class => [
            Listeners\SendClientOrderNotification::class,
            Listeners\SendAdminOrderNotification::class,
        ],
        Events\OrderPaymentTypeChanging::class => [
            Listeners\ProcessOrderPaymentListener::class,
        ],
        Events\OrderIsDone::class => [
            Listeners\UpdatePromoDiverseFoodClientStatListener::class,
            Listeners\UpdatePromoFavoriteAssortmentVariantListener::class,
            Listeners\UpdatePromoInTheShopListener::class,
        ],
        Events\OrderProductChanged::class => [
            Listeners\ProduceOrderRelatedWarehouseTransactions::class
        ],
        Events\RatingScoreCreated::class => [
            Listeners\UpdatePromoDiverseFoodClientStatListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
