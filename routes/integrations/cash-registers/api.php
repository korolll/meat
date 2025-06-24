<?php

/** @var \Illuminate\Routing\Router $router */

$router->get('/loyalty-cards/find', 'LoyaltyCardController@find')->name('loyalty-cards.find');
$router->post('/loyalty-cards/associate', 'LoyaltyCardController@associate')->name('loyalty-cards.associate');

$router->post('/webhook/update-stop-list', 'WebhookController@updateStopList')->name('webhook.update-stop-list');
$router->post('/receipts/calculate-discount', 'ReceiptController@calculateDiscount')->name('receipts.calculate-discount');
$router->post('/receipts', 'ReceiptController@store')->name('receipts.store');

$router->get('/loyalty-cards/findloyaltycardbyphone', 'LoyaltyCardController@findloyaltycardbyphone')->name('loyalty-cards.findloyaltycardbyphone');
