<?php
use Illuminate\Routing\Router;

$router->group(["namespace" => "App\Controllers"], function (Router $router) {
    $router->get('/fetch', "FetchController@get");
    $router->get('/calculate', "CalculateController@index")->name("calculate");
    $router->get("/compute", "ComputeController@index");
    $router->get("/hash", "HashController@index");
    $router->get("/hash/force", "HashController@force");
    $router->get("/tinker", "TinkerController@index");
    $router->get("/block", "BlockController@index");
});
