<?php
declare(strict_types = 1);
use Cake\Routing\Router;

Router::plugin('AuthActions', function ($routes): void {
    $routes->fallbacks();
});
