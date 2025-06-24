<?php

namespace App\Providers\Management\Client;

use App\Services\Management\Client\Bonus\BonusTransactionProducer;
use App\Services\Management\Client\Bonus\BonusTransactionProducerInterface;
use App\Services\Management\Client\Bonus\MaxBonusesCalculator;
use App\Services\Management\Client\Bonus\MaxBonusesCalculatorInterface;
use App\Services\Management\Client\Order\OrderDeliveryPriceCalculator;
use App\Services\Management\Client\Order\OrderDeliveryPriceCalculatorInterface;
use App\Services\Management\Client\Order\OrderFactory;
use App\Services\Management\Client\Order\OrderFactoryInterface;
use App\Services\Management\Client\Order\OrderFinalPriceResolver;
use App\Services\Management\Client\Order\OrderFinalPriceResolverInterface;
use App\Services\Management\Client\Order\OrderLocker;
use App\Services\Management\Client\Order\OrderLockerInterface;
use App\Services\Management\Client\Order\OrderPriceResolver;
use App\Services\Management\Client\Order\OrderPriceResolverInterface;
use App\Services\Management\Client\Order\OrderProductChanger;
use App\Services\Management\Client\Order\OrderProductChangerInterface;
use App\Services\Management\Client\Order\OrderSyncUpdater;
use App\Services\Management\Client\Order\OrderSyncUpdaterInterface;
use App\Services\Management\Client\Order\Payment\OrderPaymentProcessor;
use App\Services\Management\Client\Order\Payment\OrderPaymentProcessorInterface;
use App\Services\Management\Client\Order\Payment\PaymentOrderBundleGenerator;
use App\Services\Management\Client\Order\Payment\PaymentOrderBundleGeneratorInterface;
use App\Services\Management\Client\Order\StatusTransitionManager;
use App\Services\Management\Client\Order\StatusTransitionManagerInterface;
use App\Services\Management\Client\Order\System\SystemOrderSettingStorage;
use App\Services\Management\Client\Order\System\SystemOrderSettingStorageInterface;
use App\Services\Management\Client\Product\ClientBulkProductPriceCalculatorInterface;
use App\Services\Management\Client\Product\ClientProductCollectionPriceCalculator;
use App\Services\Management\Client\Product\ClientProductCollectionPriceCalculatorInterface;
use App\Services\Management\Client\Product\ClientProductPaidBonusApplier;
use App\Services\Management\Client\Product\ClientProductPaidBonusApplierInterface;
use App\Services\Management\Client\Product\ClientProductPriceCalculator;
use App\Services\Management\Client\Product\ClientProductPriceCalculatorInterface;
use App\Services\Management\Client\Product\Discount\ClientProductCollectionDiscountResolver;
use App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverInterface;
use App\Services\Management\Client\Product\Discount\ClientProductDiscountResolverPreloadInterface;
use App\Services\Management\Client\Product\Discount\Concrete\DiverseFoodPriceDiscountResolver;
use App\Services\Management\Client\Product\Discount\Concrete\FavoriteAssortmentDiscountResolver;
use App\Services\Management\Client\Product\Discount\Concrete\FirstOrderDiscountResolver;
use App\Services\Management\Client\Product\Discount\Concrete\FrontolInMemoryDiscount;
use App\Services\Management\Client\Product\Discount\Concrete\InTheShopDiscountResolver;
use App\Services\Management\Client\Product\Discount\Concrete\YellowPriceDiscountResolver;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolver;
use App\Services\Management\Client\Product\Discount\PromoDescriptionResolverInterface;
use App\Services\Management\Client\Product\SimpleClientBulkProductPriceCalculator;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ProductPriceServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public array $singletons = [
        ClientProductPriceCalculatorInterface::class => ClientProductPriceCalculator::class,
        ClientProductCollectionPriceCalculatorInterface::class => ClientProductCollectionPriceCalculator::class,
        ClientProductPaidBonusApplierInterface::class => ClientProductPaidBonusApplier::class,
        ClientBulkProductPriceCalculatorInterface::class => SimpleClientBulkProductPriceCalculator::class,
        ClientProductDiscountResolverInterface::class => ClientProductCollectionDiscountResolver::class,
        ClientProductDiscountResolverPreloadInterface::class => ClientProductCollectionDiscountResolver::class,
        OrderFinalPriceResolverInterface::class => OrderFinalPriceResolver::class,
        OrderProductChangerInterface::class => OrderProductChanger::class,
        OrderLockerInterface::class => OrderLocker::class,
        OrderSyncUpdaterInterface::class => OrderSyncUpdater::class,
        OrderPriceResolverInterface::class => OrderPriceResolver::class,
        StatusTransitionManagerInterface::class => StatusTransitionManager::class,
        OrderFactoryInterface::class => OrderFactory::class,
        OrderDeliveryPriceCalculatorInterface::class => OrderDeliveryPriceCalculator::class,
        PaymentOrderBundleGeneratorInterface::class => PaymentOrderBundleGenerator::class,
        PromoDescriptionResolverInterface::class => PromoDescriptionResolver::class,
        BonusTransactionProducerInterface::class => BonusTransactionProducer::class,
        MaxBonusesCalculatorInterface::class => MaxBonusesCalculator::class,
        OrderPaymentProcessorInterface::class => OrderPaymentProcessor::class,
        DiverseFoodPriceDiscountResolver::class => DiverseFoodPriceDiscountResolver::class,
        FavoriteAssortmentDiscountResolver::class => FavoriteAssortmentDiscountResolver::class,
        FirstOrderDiscountResolver::class => FirstOrderDiscountResolver::class,
        FrontolInMemoryDiscount::class => FrontolInMemoryDiscount::class,
        InTheShopDiscountResolver::class => InTheShopDiscountResolver::class,
        YellowPriceDiscountResolver::class => YellowPriceDiscountResolver::class,
        SystemOrderSettingStorageInterface::class => SystemOrderSettingStorage::class,
    ];

    /**
     *
     */
    public function register()
    {
        $this->app->when(ClientProductCollectionDiscountResolver::class)->needs('$resolvers')->give(function () {
            $classes = config('app.clients.discount.resolvers', []);
            $resolvers = [];
            foreach ($classes as $class) {
                $resolvers[] = $this->app->make($class);
            }

            return $resolvers;
        });

        $this->app->when(StatusTransitionManager::class)->needs('$transitionVariants')->give(function () {
            return config('app.order.status-transitions');
        });

        $this->app->when(OrderDeliveryPriceCalculator::class)->needs('$config')->give(function () {
            return config('app.order.price.delivery');
        });

        $this->app->when(MaxBonusesCalculator::class)->needs('$maxBonusPercentToPay')->give(function () {
            return (float)config('app.order.price.bonus.max_percent_to_pay');
        });

        $this->app->when(FirstOrderDiscountResolver::class)->needs('$config')->give(function () {
            return (array)config('app.order.price.first_order_discount_resolver_config');
        });
    }


    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
