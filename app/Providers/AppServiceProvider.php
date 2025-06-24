<?php

namespace App\Providers;

use App\Contracts\Management\Product\ByAssortmentProductMakerContract;
use App\Http\Middleware\API\LogRequestResponse;
use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientCreditCard;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PaymentVendor;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\Stocktaking;
use App\Models\User;
use App\Models\WriteOff;
use App\Services\Debug\DebugDataCollector;
use App\Services\Framework\CacheProfile\CacheSuccessfulGetRequestsByConfig;
use App\Services\Framework\Database\PostgresConnection;
use App\Services\Framework\Notifications\Channels\MegafonSmsChannel;
use App\Services\Framework\Notifications\Channels\SmsChannelContract;
use App\Services\Framework\Routing\Router;
use App\Services\Framework\Validation\Validator as TealsyValidator;
use App\Services\Management\PriceList\ProductManager;
use App\Services\Management\PriceList\ProductManagerContract;
use App\Services\Management\Product\ByAssortmentProductMaker;
use App\Services\Management\Product\Contracts\ProductReplicatorContract;
use App\Services\Management\Product\ProductReplicator;
use App\Services\Management\ProductRequest\DeliveryUserApplier;
use App\Services\Management\ProductRequest\DeliveryUserApplierContract;
use App\Services\Management\ProductRequest\ProductRequestExpectedDeliveryDateValidator;
use App\Services\Management\ProductRequest\ProductRequestExpectedDeliveryDateValidatorContract;
use App\Services\Management\ProductRequest\ProductRequestFactory;
use App\Services\Management\ProductRequest\ProductRequestFactoryContract;
use App\Services\Management\ProductRequest\ProductRequestWrapper;
use App\Services\Management\ProductRequest\ProductRequestWrapperContract;
use App\Services\Management\ProductRequest\ProductRequestSelfDeliveryProvider;
use App\Services\Management\ProductRequest\ProductRequestSelfDeliveryProviderContract;
use App\Services\Management\ProductRequest\StatusTransitionManager;
use App\Services\Management\ProductRequest\StatusTransitionManagerContract;
use App\Services\Management\Transportation\TransportationPointFactory;
use App\Services\Management\Transportation\TransportationPointFactoryContract;
use App\Services\Management\Transportation\TransportationPointOrderApplier;
use App\Services\Management\Transportation\TransportationPointOrderApplierContract;
use App\Services\Management\Transportation\TransportationPointOrderValidator;
use App\Services\Management\Transportation\TransportationPointOrderValidatorContract;
use App\Services\Money\Acquire\AcquireInterface;
use App\Services\Money\Acquire\Resolver\AcquireResolver;
use App\Services\Money\Acquire\Resolver\AcquireResolverInterface;
use App\Services\Money\Acquire\SberbankAcquire;
use App\Services\Money\Acquire\YooKassaAcquire;
use Arhitector\Yandex\Disk;
use Carbon\CarbonImmutable;
use Carbon\Factory as CarbonFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\LogMiddleware;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use App\Services\Management\LaboratoryTest\StatusTransitionManager as LaboratoryStatusTransitionManager;
use App\Contracts\Management\LaboratoryTest\StatusTransitionManagerContract as LaboratoryStatusTransitionManagerContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Builder::defaultMorphKeyType('uuid');
        Date::use(new CarbonFactory(
            ['toJsonFormat' => 'Y-m-d H:i:sO', 'toStringFormat' => 'Y-m-d H:i:sO'],
            CarbonImmutable::class
        ));

        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PostgresConnection($connection, $database, $prefix, $config);
        });

        Notification::extend('sms', function () {
            return app(SmsChannelContract::class);
        });

        Relation::morphMap([
            Assortment::MORPH_TYPE_ALIAS => Assortment::class,
            Client::MORPH_TYPE_ALIAS => Client::class,
            ClientCreditCard::MORPH_TYPE_ALIAS => ClientCreditCard::class,
            Order::MORPH_TYPE_ALIAS => Order::class,
            OrderProduct::MORPH_TYPE_ALIAS => OrderProduct::class,
            CustomerProductRequest::MORPH_TYPE_ALIAS => CustomerProductRequest::class,
            Receipt::MORPH_TYPE_ALIAS => Receipt::class,
            ReceiptLine::MORPH_TYPE_ALIAS => ReceiptLine::class,
            Stocktaking::MORPH_TYPE_ALIAS => Stocktaking::class,
            SupplierProductRequest::MORPH_TYPE_ALIAS => SupplierProductRequest::class,
            User::MORPH_TYPE_ALIAS => User::class,
            WriteOff::MORPH_TYPE_ALIAS => WriteOff::class,
        ]);

        Validator::resolver(function ($translator, $data, $rules, $messages, $customAttributes) {
            return new TealsyValidator($translator, $data, $rules, $messages, $customAttributes);
        });

        $this->app->when(CacheSuccessfulGetRequestsByConfig::class)->needs('$config')->give(function () {
            return config('responsecache.cache_routes');
        });

        if (config('app.debug_queries')) {
            DB::listen(function (QueryExecuted $query) {
                logger()->channel('queries')->debug($query->sql, $query->bindings);
            });
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(DebugDataCollector::class);
        $this->app->singleton(LogRequestResponse::class);
        $this->app->bind(ClientInterface::class, function ($app) {
            /**
             * @var $app Application
             */
            $params = [];
            if (config('services.guzzle_debug') === true) {
                $stack = HandlerStack::create();
                $stack->push(new LogMiddleware(logger()));
                $params = ['handler' => $stack];
            }

            return new \GuzzleHttp\Client($params);
        });

        // Роутер
        $this->app->bind(Registrar::class, Router::class);

        // Уведомления
        $this->app->bind(SmsChannelContract::class, MegafonSmsChannel::class);

        // Менеджер переходов статусов заявок на товары
        $this->app->bind(DeliveryUserApplierContract::class, DeliveryUserApplier::class);
        $this->app->bind(StatusTransitionManagerContract::class, StatusTransitionManager::class);
        $this->app->when(StatusTransitionManager::class)->needs('$transitionVariants')->give(function () {
            return config('app.product-request.status-transitions');
        });

        // Менеджер синхронизации товаров в прайс-листах
        $this->app->singleton(ProductManagerContract::class, ProductManager::class);

        // Фабрика заявок и промежуточная обертка для заявки
        $this->app->bind(ProductRequestFactoryContract::class, ProductRequestFactory::class);
        $this->app->bind(ProductRequestWrapperContract::class, ProductRequestWrapper::class);
        // Валидатор предполагаемой даты доставки для заявки
        $this->app->bind(ProductRequestExpectedDeliveryDateValidatorContract::class,
            ProductRequestExpectedDeliveryDateValidator::class);

        // Провайдер для установки состояния заяки для доставки - "самовывоз"
        $this->app->singleton(ProductRequestSelfDeliveryProviderContract::class, ProductRequestSelfDeliveryProvider::class);

        // Рейсы
        $this->app->bind(TransportationPointFactoryContract::class, TransportationPointFactory::class);
        $this->app->bind(TransportationPointOrderApplierContract::class, TransportationPointOrderApplier::class);
        $this->app->bind(TransportationPointOrderValidatorContract::class, TransportationPointOrderValidator::class);

        // Товары
        $this->app->bind(ProductReplicatorContract::class, ProductReplicator::class);
        // Менеджер переходов статусов лабораторных заявок
        $this->app->bind(LaboratoryStatusTransitionManagerContract::class, LaboratoryStatusTransitionManager::class);
        $this->app->when(LaboratoryStatusTransitionManager::class)->needs('$transitionVariants')->give(function () {
            return config('app.laboratory-test.status-transitions');
        });

        // Из ассортиментной матрицы в товары
        $this->app->bind(ByAssortmentProductMakerContract::class, ByAssortmentProductMaker::class);

        // Money -> Эквайринг
        $this->app->singleton(AcquireResolverInterface::class, AcquireResolver::class);
        $this->app->when(AcquireResolver::class)->needs('$vendorToConfig')->give(function () {
            return [
                PaymentVendor::ID_SBERBANK => (array)config('services.sberbank.acquire.config', []),
                PaymentVendor::ID_YOOKASSA => (array)config('services.yookassa.acquire.config', []),
            ];
        });
        $this->app->when(AcquireResolver::class)->needs('$vendorToClass')->give(function () {
            return [
                PaymentVendor::ID_SBERBANK => SberbankAcquire::class,
                PaymentVendor::ID_YOOKASSA => YooKassaAcquire::class,
            ];
        });

        // Yandex disk
        $this->app->singleton(Disk::class);
        $this->app->when(Disk::class)->needs('$token')->give(function () {
            return (string)config('services.yandex.disk.token', '');
        });
    }
}
