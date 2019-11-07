<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Carbon\Carbon;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/cache', function () use ($router) {
    app('redis')->set('ping', 'PONG');

    return app('redis')->get('ping');
});

$router->get('/reset', function () use ($router) {
    app('redis')->set(env('OPTION_LATEST_DATE_CRAWLED'), Carbon::now()->format('Y-m-d'));
});
