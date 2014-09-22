<?php
use Cake\Routing\Router;

Router::plugin('AuthActions', function ($routes) {
	$routes->fallbacks();
});
