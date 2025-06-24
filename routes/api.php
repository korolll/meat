<?php

/** @var \Illuminate\Routing\Router $router */

// Регистрация пользователя
$router->group(['prefix' => 'users', 'as' => 'users.'], function () use ($router) {
    $router->post('register', 'UserRegisterController@register')->name('register');

    // Восстановление пароля
    $router->post('reset-password', 'UserPasswordResetController@resetPassword')->name('reset-password');
    $router->post('reset-password/set-password', 'UserPasswordResetController@setPassword')->name('reset-password.set-password');
    $router->post('reset-password/validate-token', 'UserPasswordResetController@validateToken')->name('reset-password.validate-token');

    // Подтверждение почты
    $router->post('verify-email', 'UserRegisterController@verifyEmail')->name('verify-email');

    // Проверка подтвержденной почты
    $router->post('email-verified', 'UserRegisterController@emailVerified')->name('email-verified');
});

// Список регионов
$router->get('regions', 'RegionController@index')->name('regions');

// Список контактов
$router->get('contacts', 'AppContactController@index');
$router->put('contacts', 'AppContactController@update');

// Типы подписантов
$router->get('signer_types', 'SignerTypeController@index')->name('signer_types');

// Подсказки
$router->get('suggestions/banks', 'SuggestionController@banks')->name('suggestions.banks');
$router->get('suggestions/organizations', 'SuggestionController@organizations')->name('suggestions.organizations');

// Доступно только после авторизации
$router->group(['middleware' => ['auth.basic']], function () use ($router) {
    // Аналитика по заказам
    $router->post('order-analytics', 'OrderController@getOrderAnalytics');
    // Доступно только после подтверждения почты
    $router->group(['middleware' => ['user.email-verified']], function () use ($router) {
        // Профиль пользователя
        $router->get('/profile', 'ProfileController@show')->name('profile.show');
        $router->put('/profile', 'ProfileController@update')->name('profile.update');
        $router->get('/profile/supply-contract', 'ProfileController@supplyContract')->name('profile.supply-contract');

        // Загрузка файлов
        $router->post('files', 'FileController@store')->name('files');
    });

    // Только с авторизацией
    $router->group(['middleware' => ['user.approved']], function () use ($router) {
        // Мониторинг заявок
        $router->group(['namespace' => 'ProductRequests', 'prefix' => 'product-requests', 'as' => 'product-requests.'], function () use ($router) {
            // Мониторинг заявок на транспортировку
            $router->apiResource('delivery', 'DeliveryProductRequestController')->only('index');
        });

        // Профиль пользователя
        $router->group(['namespace' => 'Profile', 'prefix' => 'profile', 'as' => 'profile.'], function () use ($router) {
            // Заявки
            $router->group(['namespace' => 'ProductRequests', 'prefix' => 'product-requests', 'as' => 'product-requests.'], function () use ($router) {
                // Заявки на пополнение
                $router->apiResource('customer', 'CustomerProductRequestController')->only([
                    'index',
                    'store',
                    'show',
                ])->parameters([
                    'customer' => 'product_request',
                ]);

                $router->get('customer/{product_request}/products', 'CustomerProductRequestController@products')->name('customer.pr.products');
                $router->put('customer/{product_request}/set-status', 'CustomerProductRequestController@setStatus')->name('customer.pr.set-status');
                $router->get('customer/{product_request}/export/xlsx', 'CustomerProductRequestController@export')->name('customer.pr.export.xlsx');
                $router->put('customer/{product_request}/products/{product_uuid}', 'CustomerProductRequestController@updateProduct')->name('customer.pr.products.product');

                // Заявки на отгрузку
                $router->apiResource('supplier', 'SupplierProductRequestController')->only([
                    'index',
                    'show',
                ])->parameters([
                    'supplier' => 'product_request',
                ]);

                $router->get('supplier/{product_request}/products', 'SupplierProductRequestController@products')->name('supplier.pr.products');
                $router->put('supplier/{product_request}/set-status', 'SupplierProductRequestController@setStatus')->name('supplier.pr.set-status');
                $router->put('supplier/{supplier_product_request}/set-confirmed-date', 'SupplierProductRequestController@setConfirmedDate')->name('supplier.spr.set-confirmed-date');

                // Заявки на транспортировку
                $router->apiResource('delivery', 'DeliveryProductRequestController')->only([
                    'index',
                    'store',
                    'show',
                ])->parameters([
                    'delivery' => 'product_request',
                ]);

                $router->get('delivery/{product_request}/products', 'DeliveryProductRequestController@products')->name('delivery.pr.products');

                // Презаявки
                $router->get('supplier/{product_request}/pre-requests', 'ProductPreRequestController@index')->name('supplier.pr.pre-requests');

                // Импорт заявок
                $router->post('import/{supplier_user}', 'ProductRequestImportController@import')->name('import.supplier-user');
            });

            // Формирование виртуальной ассотриментной матрицы из заявок
            $router->get('assortment-matrix/from-supplier-product-requests', 'AssortmentMatrixController@indexFromSupplierRequests')->name('assortment-matrix.from-supplier-product-requests');

            // Управление ассотриментной матрицей
            $router->apiResource('assortment-matrix', 'AssortmentMatrixController')->except([
                'show',
                'update',
            ]);

            // Управление автомобилями
            $router->apiResource('cars', 'CarController');

            // Управление каталогом
            $router->apiResource('catalogs', 'CatalogController');

            // Черновики
            $router->apiResource('drafts', 'DraftController')->only([
                'store',
                'show',
                'destroy',
            ])->parameters([
                'drafts' => 'name',
            ]);

            // Управление автомобилями
            $router->apiResource('drivers', 'DriverController');

            // Управление типами карт лояльности
            $router->apiResource('loyalty-card-types', 'LoyaltyCardTypeController')->only('index');

            // Управление прайс-листами
            $router->apiResource('price-lists', 'PriceListController');

            // Прайс-листы: экспорт в xlsx
            $router->get('price-lists/{price_list}/export/xlsx', 'PriceListController@export')->name('price-lists.price_list.export.xlsx');

            // Прайс-листы: синхронизация товарав
            $router->post('price-lists/{price_list}/products/synchronize', 'PriceListProductController@synchronize')->name('price-lists.price_list.products.synchronize');

            // Прайс-листы: управление товарами
            $router->apiResource('price-lists/{price_list}/products', 'PriceListProductController')->only([
                'index',
                'update',
            ])->names([
                'index' => 'price-lists.price-list.products.index',
                'update' => 'price-lists.price-list.products.update',
            ]);

            // Для фильтров
            $router->post('price-lists/{price_list}/products', 'PriceListProductController@index')->name('price-lists.price_list.products');

            // Массовое обновление цен продуктов
            $router->post('price-lists/{price_list}/products/batch-update', 'PriceListProductController@batchUpdate')->name('price-lists.price_list.products.batch-update');

            // Управление товарами: Массовое обновление дней отгрузки
            $router->put('products/set-delivery-weekdays', 'ProductController@setDeliveryWeekdays')->name('products.set-delivery-weekdays');

            // Управление товарами: Доступность товара для заказа
            $router->put('products/{product}/set-is-active', 'ProductController@setIsActive')->name('products.product.set-is-active');

            // Управление товарами
            $router->apiResource('products', 'ProductController')->except('destroy');

            // Товарные операции: Инвентаризация
            $router->apiResource('stocktakings', 'StocktakingController')->only([
                'index',
                'store',
            ]);

            // Товарные операции: Инвентаризация, подтверждение
            $router->put('stocktakings/{stocktaking}/approve', 'StocktakingController@approve')->name('stocktakings.stocktaking.approve');

            // Товарные операции: Инвентаризация, управление товарами
            $router->post('stocktakings/{stocktaking}/products/batch-update', 'StocktakingProductController@batchUpdate')->name('stocktakings.stocktaking.products.batch-update');
            $router->apiResource('stocktakings/{stocktaking}/products', 'StocktakingProductController')->only([
                'index',
                'update',
            ])->names([
                'index' => 'stocktakings.stocktaking.products.index',
                'update' => 'stocktakings.stocktaking.products.update',
            ]);

            // Для фильтров
            $router->post('stocktakings/{stocktaking}/products', 'StocktakingProductController@store')->name('stocktakings.stocktaking.products');


            // Управление рейсами: начало рейса, рейсы
            $router->put('transportations/{transportation}/set-started', 'TransportationController@setStarted')->name('transportations.transportation.set-started');
            $router->apiResource('transportations', 'TransportationController')->except('destroy');

            // Управление рейсами: прибытие на точку, сортировка точек, точки
            $router->put('transportations/{transportation}/points/set-order', 'TransportationPointController@setOrder')->name('transportations.transportation.points.set-order');
            $router->put('transportations/{transportation}/points/{point}/set-arrived', 'TransportationPointController@setArrived')->name('transportations.transportation.points.point.set-arrived');
            $router->apiResource('transportations/{transportation}/points', 'TransportationPointController')->only('index')
                ->names([
                    'index' => 'transportations.transportation.points.index'
                ]);

            // Товарные операции: Списания
            $router->apiResource('write-offs', 'WriteOffController')->only('store');
            $router->post('write-offs-batch', 'WriteOffController@storeBatch')->name('write-offs-batch');

            // Лабораторные исследования
            $router->group(['namespace' => 'LaboratoryTests', 'prefix' => 'laboratory-tests', 'as' => 'laboratory-tests.'], function () use ($router) {

                // Лабораторные исследования для заказчика
                $router->put('customer/{laboratory_test}/set-status', 'CustomerLaboratoryTestController@setStatus')->name('customer.laboratory_test.set-status');
                $router->apiResource('customer', 'CustomerLaboratoryTestController')->only([
                    'index',
                    'store',
                    'update',
                    'show',
                ])->parameters([
                    'customer' => 'laboratory_test',
                ]);


                // Лабораторные исследования для исполнителя
                $router->put('executor/{laboratory_test}/set-status', 'ExecutorLaboratoryTestController@setStatus')->name('executor.laboratory_test.set-status');
                $router->apiResource('executor', 'ExecutorLaboratoryTestController')->only([
                    'index',
                    'show',
                ])->parameters([
                    'executor' => 'laboratory_test',
                ]);
            });

            // Заказы
            $router->post('orders/{order}/set-status', 'OrderController@setStatus')->name('orders.order.set-status');
            $router->apiResource('orders', 'OrderController')->only([
                'index',
                'show'
            ]);
        });

        // Отчеты
        $router->group(['namespace' => 'Reports', 'prefix' => 'reports', 'as' => 'reports.'], function () use ($router) {
            // Сводный по продукам
            $router->apiResource('products-summary', 'ProductsSummaryController')->only([
                'index',
                'show',
            ])->parameters([
                'products-summary' => 'product',
            ]);

            // Сводный по чекам
            $router->apiResource('receipts-summary', 'ReceiptsSummaryController')->only([
                'index'
            ]);
            $router->get('receipts-summary/xlsx', 'ReceiptsSummaryController@export')->name('receipts-summary.xlsx');

            // Отчёт по продажам (чекам)
            $router->get('sales-report', 'SalesReportController@index')->name('sales-report');
            $router->get('sales-report/xlsx', 'SalesReportController@export')->name('sales-report.xlsx');

            // Отчёт по всем продажам
            $router->get('purchases-report', 'PurchasesReportController@report')->name('purchases-report');
            $router->get('purchases-actions-report', 'PurchasesReportController@actionsReport')->name('purchases-actions-report');
        });

        // Номеклатуры: поиск по штрихкоду
        $router->get('assortments/find-by-barcode', 'AssortmentController@findByBarcode')->name('assortments.find-by-barcode');

        // Номеклатуры: поиск товаров по uuid номенклатур
        $router->get('assortments/find-products', 'AssortmentController@findProducts')->name('assortments.find-products');

        // Номеклатуры: управление
        $router->apiResource('assortments', 'AssortmentController')->except('destroy');

        // Номеклатуры: просмотр списка товаров
        $router->get('assortments/{assortment}/products', 'AssortmentController@products')->name('assortments.assortment.products');

        // Номеклатуры: подтверждение/отклонение
        $router->put('assortments/{assortment}/verify', 'AssortmentController@verify')->name('assortments.assortment.verify');

        // Номеклатуры: бренды
        $router->apiResource('assortment-brands', 'AssortmentBrandController');

        // Номеклатуры: характеристики
        $router->apiResource('assortment-properties', 'AssortmentPropertyController');
        // Номеклатуры: характеристики - добавление доступного типа
        $router->post('assortment-properties/{assortmentProperty}/add-available-value', 'AssortmentPropertyController@addAvailableValue')->name('assortment-properties.assortmentProperty.add-available-value');
        // Номеклатуры: характеристики - удаление доступного типа
        $router->post('assortment-properties/{assortmentProperty}/remove-available-value', 'AssortmentPropertyController@removeAvailableValue')->name('assortment-properties.assortmentProperty.remove-available-value');
        // Номеклатуры: характеристики - изменение типа данных
        $router->post('assortment-properties/{assortmentProperty}/change-data-type', 'AssortmentPropertyController@changeDataType')->name('assortment-properties.assortmentProperty.change-data-type');

        // Номеклатуры: типы данных характеристик
        $router->apiResource('assortment-property-data-types', 'AssortmentPropertyDataTypeController')->only([
            'index',
            'show'
        ]);

        // Номеклатуры: список едениц измерения
        $router->get('assortment-units', 'AssortmentUnitController@index')->name('assortment-units');

        // Управление каталогом Тилси
        $router->apiResource('catalogs', 'CatalogController');

        // Управление каталогом Тилси: управление характеристиками номеклатур
        $router->apiResource('catalogs/{catalog}/assortment-properties', 'CatalogAssortmentPropertyController')->only([
            'index',
            'store',
            'destroy'
        ])->names([
            'index' => 'catalogs.catalog.assortment-properties.index',
            'store' => 'catalogs.catalog.assortment-properties.store',
            'destroy' => 'catalogs.catalog.assortment-properties.destroy',
        ]);

        // Список стран
        $router->get('countries', 'CountryController@index')->name('countries');

        // Карты лояльности
        $router->apiResource('loyalty-cards', 'LoyaltyCardController')->except('destroy');

        // Типы карт лояльности
        $router->apiResource('loyalty-card-types', 'LoyaltyCardTypeController')->except('destroy');

        // Описания акций
        $router->apiResource('promo-descriptions', 'PromoDescriptionController');

        // Список возможных значений НДС
        $router->get('nds-percents', 'NdsPercentController@index')->name('nds-percents');

        // Управление пользователями
        $router->apiResource('users', 'UserController')->only('index', 'update', 'show');

        // Управление пользователями: управление типами карт лояльности пользователя
        $router->apiResource('users/{user}/loyalty-card-types', 'UserLoyaltyCardTypeController')->only([
            'index',
            'store',
            'destroy'
        ])->names([
            'index' => 'users.user.loyalty-card-types.index',
            'store' => 'users.user.loyalty-card-types.store',
            'destroy' => 'users.user.loyalty-card-types.destroy',
        ]);

        // Пользователи: подтверждение/отклонение
        $router->put('users/{user}/verify', 'UserController@verify')->name('users.user.verify');

        // Оценки номенклатур от клиентов
        $router->get('rating-scores/assortments/clients', 'RatingScoreController@findForAssortmentsByClients')->name('rating-scores.assortments.clients');

        // Лабораторные исследования
        $router->put('laboratory-tests/{laboratory_test}/set-in-work', 'LaboratoryTestController@setInWork')->name('laboratory-tests.laboratory_test.set-in-work');
        $router->apiResource('laboratory-tests', 'LaboratoryTestController')->only('index', 'show');

        // Лабораторные исследования - Типы
        $router->apiResource('laboratory-test-appeal-types', 'LaboratoryTestAppealTypeController');

        // Лабораторные исследования - Статусы
        $router->apiResource('laboratory-test-statuses', 'LaboratoryTestStatusController')->only('index', 'show');

        // Настройки акции "Разнообразное питание"
        $router->apiResource('promo-diverse-food-settings', 'PromoDiverseFoodSettingsController')
            ->only('store', 'update', 'show', 'index', 'destroy');
        $router->post('promo-diverse-food-settings/{promoDiverseFoodSetting}/toggle-enable', 'PromoDiverseFoodSettingsController@toggleEnable')
            ->name('promo.diverse-food.settings');

        // Настройки акции "Любимый продукт"
        $router->apiResource('promo-favorite-assortment-settings', 'PromoFavoriteAssortmentSettingController')
            ->only('store', 'update', 'show', 'index')
            ->parameter('promo-favorite-assortment-settings', 'setting');
        $router->post('promo-favorite-assortment-settings/{setting}/toggle-enable', 'PromoFavoriteAssortmentSettingController@toggleEnable')
            ->name('promo.favorite-assortments-settings.toggle-enable');

        // Теги: поиск + управление
        $router->get('tags/search', 'TagController@search')->name('tags.search');
        $router->apiResource('tags', 'TagController');

        $router->post('promo-yellow-prices/bulk', 'PromoYellowPriceController@bulkStore');
        $router->apiResource('promo-yellow-prices', 'PromoYellowPriceController')
            ->only('store', 'update', 'show', 'index', 'destroy');
        $router->post('promo-yellow-prices/{promoYellowPrice}/toggle-enable', 'PromoYellowPriceController@toggleEnable');

        // Уведомления
        $router->apiResource('notifications', 'NotificationController')->only([
            'store',
        ]);

        // Клиенты
        $router->apiResource('clients', 'ClientController')->only([
            'index',
        ]);

        // Заказы
        $router->group(['prefix' => 'orders', 'as' => 'orders.'], function () use ($router) {
            $router->post('{order}/set-status', 'OrderController@setStatus')->name('order.set-status');
            $router->get('{order}/retry-payment/{deposit?}', 'OrderController@retryPayment')
                ->where('deposit', '[0-1]')
                ->name('order.retry-payment');

            $router->group(['namespace' => 'Orders'], function () use ($router) {
                // Продукты заказа
                $router->apiResource('products', 'OrderProductController')->only([
                    'show',
                    'store',
                    'update',
                ]);
            });
        });

        $router->apiResource('orders', 'OrderController')->only([
            'index',
            'show',
            'update',
        ]);

        // Настройки заказов
        $router->apiResource('order-settings', 'SystemOrderSettingController')->only([
            'index',
            'show',
            'update',
        ]);

        // Настройки по платежным системам
        $router->apiResource('payment-vendor-settings', 'PaymentVendorSettingController')->only([
            'index',
            'show',
            'store',
            'update',
        ]);

        $router->post('discount-forbidden-catalogs/store-bulk', 'DiscountForbiddenCatalogController@storeBulk')->name('discount-forbidden-catalogs.store-bulk');
        $router->apiResource('discount-forbidden-catalogs', 'DiscountForbiddenCatalogController')->only([
            'index',
            'store',
            'show',
            'destroy',
        ]);

        $router->post('discount-forbidden-assortments/store-bulk', 'DiscountForbiddenAssortmentController@storeBulk')->name('discount-forbidden-assortments.store-bulk');
        $router->apiResource('discount-forbidden-assortments', 'DiscountForbiddenAssortmentController')->only([
            'index',
            'store',
            'show',
            'destroy',
        ]);

        $router->apiResource('receipts', 'ReceiptController')->only([
            'index',
            'show',
        ]);
        $router->get('receipts/{receipt}/lines', 'ReceiptController@lines')->name('receipts.receipt.lines');

        // Управление вакансиями
        $router->apiResource('vacancy', 'VacancyController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ])->names([
            'index' => 'vacancy.index',
            'store' => 'vacancy.store',
            'show' => 'vacancy.show',
            'update' => 'vacancy.update',
            'destroy' => 'vacancy.destroy',
        ]);

        // Управление элементами онбординга
        $router->apiResource('onboarding', 'OnboardingController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ])->names([
            'index' => 'onboarding.index',
            'store' => 'onboarding.store',
            'show' => 'onboarding.show',
            'update' => 'onboarding.update',
            'destroy' => 'onboarding.destroy',
        ]);
        // Управление элементами соц. сети
        $router->apiResource('social', 'SocialController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ])->names([
            'index' => 'social.index',
            'store' => 'social.store',
            'show' => 'social.show',
            'update' => 'social.update',
            'destroy' => 'social.destroy',
        ]);

        // Управление задачами нотификаций
        $router->apiResource('notification-tasks', 'NotificationTaskController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);

        // Управление транзакциями клиента (бонусы)
        $router->apiResource('client-bonus-transactions', 'ClientBonusTransactionController')->only([
            'index',
            'store',
            'show',
        ]);

        // Сторисы
        $router->apiResource('stories', 'StoryController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);

        // Баннеры
        $router->apiResource('banners', 'BannerController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);

        // Промокоды
        $router->apiResource('promocodes', 'PromocodeController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);

        // Сторисы: Табы
        $router->apiResource('story-tabs', 'StoryTabController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);

        // Рецепты
        $router->apiResource('meal-receipts', 'MealReceiptController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);

        // Рецепты: Табы
        $router->apiResource('meal-receipt-tabs', 'MealReceiptTabController')->only([
            'index',
            'store',
            'show',
            'update',
            'destroy',
        ]);
    });
});
