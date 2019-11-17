<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */
$router = app(Router::class);

$router->get('settings', 'BatchSettingsController@index');

$router->post('settings/retry-batch', 'RetryBatchController@handle');