<?php

/** @var \Illuminate\Routing\Router $router */

$router->group(['prefix' => 'loyalty-system'], function () use ($router) {
    $router->post('document', 'FrontolController@document');
    $router->post('client', 'FrontolController@client');
    $router->post('extra/client', 'FrontolController@extraClient');
});
