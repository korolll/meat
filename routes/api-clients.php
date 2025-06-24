<?php

/** @var \Illuminate\Routing\Router $router */

$router->any('/test-callback', 'AuthenticationController@testCallback');

// Вход в систему с помощью номера телефона
$router->post('/auth/login-via-phone', 'AuthenticationController@loginViaPhone')->name('auth.login-via-phone');

// Вход в систему с помощью типа и номера карты лояльности
$router->post('/auth/login-via-loyalty-card', 'AuthenticationController@loginViaLoyaltyCard')->name('auth.login-via-loyalty-card');

// Сброс номера телефона с помощью типа и номера карты лояльности
$router->post('/auth/reset-via-loyalty-card', 'AuthenticationController@resetViaLoyaltyCard')->name('auth.reset-via-loyalty-card');

// Часть методов с авторизацией, часть с необязательной
$router->group(['middleware' => ['guard:api-clients']], function () use ($router) {
    // Магазины
    $router->get('stores/find-nearby', 'StoreController@findNearbyStores')->name('stores.find-nearby');
    $router->apiResource('stores', 'StoreController')->only([
        'index',
        'show'
    ]);

    // Ассортимент магазина
    $router->get('stores/{store}/assortments', 'StoreController@showAssortments')->name('stores.store.assortments');

    // Номенклатуры
    $router->get('assortments/search', 'AssortmentController@search')->name('assortments.search');
    $router->apiResource('assortments', 'AssortmentController')->only('index', 'show');

    // Публичные каталоги
    $router->apiResource('catalogs', 'CatalogController');

    // Описания акций
    $router->apiResource('promo-descriptions', 'PromoDescriptionController')->only('index');

    // Теги: поиск + список
    $router->get('tags/search', 'TagController@search')->name('tags.search');
    $router->apiResource('tags', 'TagController')->only('index', 'show');

    // Номеклатуры: бренды
    $router->apiResource('assortment-brands', 'AssortmentBrandController');

    // Список онбординг элементов
    $router->apiResource('onboarding', 'OnboardingController')->only([
        'index',
    ]);

    // Список элементов соц.сетей
    $router->apiResource('social', 'SocialController')->only([
        'index',
    ]);

    // Список сторисов
    $router->apiResource('stories', 'StoryController')->only([
        'index',
        'show',
    ]);

    // Список баннеров
    $router->apiResource('banners', 'BannerController')->only([
        'index',
        'show',
    ]);

    // Список рецептов
    $router->get('meal-receipts-unique-sections', 'MealReceiptController@uniqueSections')->name('meal-receipts-unique-sections');
    $router->post('meal-receipts/{meal_receipt}/reaction', 'MealReceiptController@reaction')->name('meal-receipts.meal-receipt.reaction');
    $router->apiResource('meal-receipts', 'MealReceiptController')->only([
        'index',
        'show',
    ]);

    // Оценки номенклатур от клиентов
    $router->get('rating-scores/assortments/clients', 'RatingScoreController@findForAssortmentsByClients')->name('rating-scores.assortments.clients');

    // Только с авторизацией
    $router->group(['middleware' => ['auth']], function () use ($router) {
        // Профиль клиента
        $router->get('profile', 'ProfileController@show')->name('profile.get');
        $router->put('profile', 'ProfileController@update')->name('profile.put');
        $router->delete('profile', 'ProfileController@delete')->name('profile.delete');
        $router->get('profile/purchases-sum', 'ProfileController@purchasesSum')->name('profile.purchases-sum');
        $router->get('profile/purchases-month', 'ProfileController@purchasesMonth')->name('profile.purchases-month');

        // Профиль клиента
        $router->group(['namespace' => 'Profile', 'prefix' => 'profile', 'as' => 'profile.'], function () use ($router) {


            // Карты лояльности
            $router->apiResource('loyalty-cards', 'LoyaltyCardController')->only([
                'index',
            ]);

            Route::apiResource('push-tokens', 'PushTokenController')->only([
                'store',
                'destroy',
            ]);

            // Чеки
            $router->apiResource('receipts', 'ReceiptController')->only([
                'index',
                'show'
            ]);

            // Чеки: Проданные позиции
            $router->put('receipts/{receipt}/lines/{receipt_line}/set-rating', 'ReceiptLineController@setRating')->name('receipts.receipt.lines.receipt_line.set-rating');
            $router->get('receipts/{receipt}/lines', 'ReceiptLineController@index')->name('receipts.receipt.lines');

            // Списки для покупки
            $router->apiResource('shopping-lists', 'ShoppingListController');

            // Ассортимент в списках покупок
            $router->apiResource(
                'shopping-lists/{shopping_list}/assortments',
                'ShoppingListAssortmentController'
            )->only([
                'store',
                'destroy',
            ])->names([
                'store' => 'shopping-lists.shopping_list.assortments.store',
                'destroy' => 'shopping-lists.shopping_list.assortments.destroy'
            ]);

            // Любимые продукты
            $router->apiResource('favorite-assortments', 'FavoriteAssortmentController')->only([
                'store',
                'destroy',
            ])->parameters([
                'favorite-assortments' => 'assortment',
            ]);

            // Варианты скидки по акции "Любимый продукт"
            $router->post('promo-favorite-assortment-variants/{variant}/activate', 'PromoFavoriteAssortmentVariantController@activateDiscount');
            $router->apiResource('promo-favorite-assortment-variants', 'PromoFavoriteAssortmentVariantController')->only([
                'index',
            ]);

            // Включенные скидки по акции "Любимый продукт"
            $router->apiResource('active-promo-favorite-assortments', 'ActivePromoFavoriteAssortmentController')->only([
                'index',
            ]);

            // Список уведомлений
            $router->apiResource('notifications', 'NotificationController')->only([
                'index',
            ]);

            // Управление уведомлениями
            Route::post('notifications/{notification}/read', 'NotificationController@read')->name('notifications.read');
            Route::post('notifications/read-all', 'NotificationController@readAll')->name('notifications.read-all');
            Route::post('notifications/delete-all', 'NotificationController@deleteAll')->name('notifications.delete-all');
            Route::post('notifications/{notification}/delete', 'NotificationController@delete')->name('notifications.delete');


            // Адреса доставки
            $router->apiResource('delivery-addresses', 'ClientDeliveryAddressController');

            // Избранные магазины
            $router->apiResource('favorite-stores', 'FavoriteStoreController')->only([
                'store',
                'destroy',
            ]);

            // Избранные рецепты
            $router->apiResource('favorite-meal-receipts', 'FavoriteMealReceiptController')->only([
                'store',
                'destroy',
            ]);

            // Акции
            $router->group(['namespace' => 'Promotions', 'prefix' => 'promotion','as' => 'promotion'], function() use ($router) {
                $router->apiResource('in-the-shop','InTheShopController')
                    ->only([
                        'index',
                        'store',
                    ]);
            });

            // Корзина
            $router->group(['prefix' => 'shopping-cart', 'as' => 'shopping-cart.'], function () use ($router) {
                $router->post('fill-from-shopping-list/{shoppingList}', 'ShoppingCartController@fillFromShoppingList')->name('fill-from-shopping-list');
                $router->post('fill-from-order/{order}', 'ShoppingCartController@fillFromOrder')->name('fill-from-order');
                $router->post('assortments/update', 'ShoppingCartController@customUpdate')->name('assortments.update');
                $router->delete('assortments', 'ShoppingCartController@clear')->name('assortments');

                $router->apiResource('assortments', 'ShoppingCartController')->only([
                    'index',
                    'store',
                    'show',
                    'destroy',
                ])->parameters([
                    'assortment' => 'assortmentUuid',
                ]);

                $router->post('assortments/bulk', 'ShoppingCartController@bulkStore');
            });

            // Заказы
            $router->post('orders/calculate', 'OrderController@calculate')->name('orders.calculate');
            $router->post('orders/{order}/set-status', 'OrderController@setStatus')->name('orders.order.set-status');
            $router->apiResource('orders', 'OrderController')->only([
                'index',
                'store',
                'update',
                'show'
            ]);
            $router->post('orders/products/{product}/set-rating', 'OrderController@setProductRating')->name('orders.products.product.set-rating');

            // Credit cards
            $router->group(['prefix' => 'credit-cards', 'as' => 'credit-cards.'], function () use ($router) {
                $router->get('link', 'CreditCardController@linkCard')->name('link');
                $router->get('link/success', 'CreditCardController@linkCardSuccess')->name('link.success');
                $router->get('link/error', 'CreditCardController@linkCardError')->name('link.error');
            });
            $router->apiResource('credit-cards', 'CreditCardController')->only([
                'index',
                'show',
                'destroy'
            ]);

            // Текущие показатели по акции "Разнообразное питание"
            $router->apiResource('promo-diverse-food-stats', 'PromoDiverseFoodStatController')->only([
                'index',
                'show',
            ])->parameter('promo-diverse-food-stats', 'stat');

            // Текущий уровень польователя в акции "Разнообразное питание"
            $router->get('promo-diverse-food-settings/future-level', 'PromoDiverseFoodSettingsController@futureLevel')->name('promo-diverse-food-settings.future-level');

            // Скидки по акции "Разнообразное питание"
            $router->apiResource('promo-diverse-food-discounts', 'PromoDiverseFoodDiscountController')->only([
                'index',
                'show',
            ])->parameter('promo-diverse-food-discounts', 'discount');

            // Управление транзакциями клиента (бонусы)
            $router->apiResource('client-bonus-transactions', 'ClientBonusTransactionController')->only([
                'index',
                'show',
            ]);
        });

        // Список стран
        $router->get('countries', 'CountryController@index')->name('countries');

        // Список контактов
        $router->get('contacts', 'AppContactController@index');

        // Список вакансий
        $router->apiResource('vacancy', 'VacancyController')->only([
            'index',
        ]);

        // Geocoding
        $router->get('geocode', 'GeocodeController@geocode')->name('geocode');
        $router->get('geocode/reverse', 'GeocodeController@reverse')->name('geocode.reverse');

        // Варианты акции "Разнообразное питание"
        $router->apiResource('promo-diverse-food-settings', 'PromoDiverseFoodSettingsController')->only([
            'index',
            'show',
        ]);
    });
});
