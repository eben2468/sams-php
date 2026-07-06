<?php

/*
|--------------------------------------------------------------------------
| SAMS — Front Controller (plain PHP)
|--------------------------------------------------------------------------
| Single entry point. All requests are routed through here by the
| public/.htaccess rewrite rules.
*/

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

define('SAMS_START', microtime(true));

$root = dirname(__DIR__);

require $root . '/app/autoload.php';

/*
| Bootstrap
*/
date_default_timezone_set(config('app.timezone', 'UTC'));

if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);
    ini_set('display_errors', '0');
}

View::setViewPath($root . '/resources/views');
Session::start();

/*
| Routing
*/
$router = new Router();
require $root . '/routes/web.php';
require $root . '/routes/api.php';

/*
| Handle the request
*/
$request = Request::capture();
$GLOBALS['__sams_request'] = $request;

$response = $router->dispatch($request);
$response->send();
