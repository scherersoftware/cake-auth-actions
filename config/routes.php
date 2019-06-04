<?php
declare(strict_types = 1);

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin('AuthActions', function (RouteBuilder $routes): void {
    $routes->fallbacks();
});
