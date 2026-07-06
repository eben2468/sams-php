<?php

/*
|--------------------------------------------------------------------------
| Application Configuration (plain PHP)
|--------------------------------------------------------------------------
| Values are pulled from the .env file by the Env loader. This replaces
| the entire Laravel config/* directory.
*/

use App\Core\Env;

Env::load(dirname(__DIR__) . '/.env');

return [
    'app' => [
        'name'     => Env::get('APP_NAME', 'SAMS - Student Attendance Management System'),
        'env'      => Env::get('APP_ENV', 'local'),
        'debug'    => filter_var(Env::get('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
        'url'      => rtrim(Env::get('APP_URL', 'http://localhost'), '/'),
        'timezone' => Env::get('APP_TIMEZONE', 'UTC'),
        'key'      => Env::get('APP_KEY', 'sams-secret-key'),
    ],

    'db' => [
        'host'     => Env::get('DB_HOST', '127.0.0.1'),
        'port'     => Env::get('DB_PORT', '3306'),
        'database' => Env::get('DB_DATABASE', 'sams_php_db'),
        'username' => Env::get('DB_USERNAME', 'root'),
        'password' => Env::get('DB_PASSWORD', ''),
        'charset'  => 'utf8mb4',
    ],

    'session' => [
        'name'     => Env::get('SESSION_NAME', 'sams_session'),
        'lifetime' => (int) Env::get('SESSION_LIFETIME', 480), // minutes
    ],

    // Where uploaded student photos are stored (relative to public/)
    'uploads' => 'uploads',
];
