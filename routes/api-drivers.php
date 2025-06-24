<?php

/** @var \Illuminate\Routing\Router $router */

// Только с авторизацией
$router->group(['middleware' => ['guard:api-drivers', 'auth.basic']], function () use ($router) {
    // Заявки на транспортировку
    $router->get('product-requests/delivery/{product_request}/products', 'DeliveryProductRequestController@products');
    $router->get('product-requests/delivery/{product_request}', 'DeliveryProductRequestController@show');

    // Эеспедирование: начало рейса, рейсы
    $router->put('transportations/{transportation}/set-started', 'TransportationController@setStarted');
    $router->get('transportations', 'TransportationController@index');

    // Эеспедирование: прибытие на точку, точки
    $router->put('transportations/{transportation}/points/{point}/set-arrived', 'TransportationPointController@setArrived');
    $router->get('transportations/{transportation}/points', 'TransportationPointController@index');
});
