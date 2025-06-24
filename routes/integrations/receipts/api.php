<?php

/** @var \Illuminate\Routing\Router $router */

$router->apiResource('receipt-packages', 'ReceiptPackageController')->only('store');
