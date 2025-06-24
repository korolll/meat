<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/** @var \Illuminate\Routing\Router $router */

$router->get('/', 'WebController@unavailable');
$router->get('/success-payment', 'WebController@successPayment')->name('success-payment');
$router->get('/error-payment', 'WebController@errorPayment')->name('error-payment');
