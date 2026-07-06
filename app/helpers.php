<?php

use App\Core\Auth;
use App\Core\AuthProxy;
use App\Core\HttpException;
use App\Core\Redirector;
use App\Core\Request;
use App\Core\ResponseFactory;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use Carbon\Carbon;

/**
 * Global helper functions. These replace the Laravel helpers the
 * application relied on (route, auth, session, old, csrf_*, etc.).
 */

if (!function_exists('config')) {
    function config(?string $key = null, $default = null)
    {
        static $config = null;
        if ($config === null) {
            $config = require dirname(__DIR__) . '/config/config.php';
        }
        if ($key === null) {
            return $config;
        }
        $value = $config;
        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('base_uri')) {
    /**
     * The URL prefix the app is mounted under (e.g. "/sams-php" or
     * "/sams-php/public" or ""). Computed from the request so links and
     * route matching work whether the app is served from a vhost docroot,
     * a subfolder, or a subfolder's /public via the root .htaccess.
     */
    function base_uri(): string
    {
        static $base = null;
        if ($base !== null) {
            return $base;
        }

        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($script), '/');
        $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $candidates = [];
        if ($scriptDir !== '' && $scriptDir !== '/' && $scriptDir !== '.') {
            $candidates[] = $scriptDir;
            // When served via the root .htaccess rewrite, the browser URL omits
            // "/public", so also consider the parent directory.
            if (basename($scriptDir) === 'public') {
                $parent = rtrim(dirname($scriptDir), '/');
                if ($parent !== '' && $parent !== '/' && $parent !== '.') {
                    $candidates[] = $parent;
                }
            }
        }

        foreach ($candidates as $candidate) {
            if ($reqPath === $candidate || str_starts_with($reqPath, $candidate . '/')) {
                return $base = $candidate;
            }
        }

        return $base = '';
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $path = '/' . ltrim($path, '/');
        return base_uri() . ($path === '/' ? '/' : rtrim($path, '/'));
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return base_uri() . '/' . ltrim($path, '/');
    }
}

if (!function_exists('media')) {
    /**
     * URL for a file stored under public/uploads, served through the PHP
     * front controller. Works even on hosts that do not serve public/uploads
     * statically. $relative is the path relative to uploads, e.g.
     * "branding/logo.png" or "students/abc.jpg".
     */
    function media(string $relative): string
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        return base_uri() . '/media?f=' . rawurlencode($relative);
    }
}

if (!function_exists('route')) {
    function route(string $name, $parameters = []): string
    {
        return Router::getInstance()->route($name, $parameters);
    }
}

if (!function_exists('request')) {
    function request(?string $key = null, $default = null)
    {
        global $__sams_request;
        /** @var Request|null $__sams_request */
        if ($key === null) {
            return $__sams_request;
        }
        return $__sams_request ? ($__sams_request->input($key) ?? $default) : $default;
    }
}

if (!function_exists('auth')) {
    function auth(): AuthProxy
    {
        return new AuthProxy();
    }
}

if (!function_exists('redirect')) {
    function redirect(?string $to = null)
    {
        $redirector = new Redirector();
        return $to === null ? $redirector : $redirector->to($to);
    }
}

if (!function_exists('response')) {
    function response(): ResponseFactory
    {
        return new ResponseFactory();
    }
}

if (!function_exists('view')) {
    function view(string $name, array $data = []): string
    {
        return View::render($name, $data);
    }
}

if (!function_exists('session')) {
    function session(?string $key = null, $default = null)
    {
        if ($key === null) {
            return new \App\Core\SessionProxy();
        }
        if (Session::hasFlash($key)) {
            return Session::getFlash($key);
        }
        return Session::get($key, $default);
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '')
    {
        $old = Session::oldInput();
        return array_key_exists($key, $old) ? $old[$key] : $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('now')) {
    function now(): Carbon
    {
        return Carbon::now();
    }
}

if (!function_exists('today')) {
    function today(): Carbon
    {
        return Carbon::today();
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): void
    {
        throw new HttpException($code, $message);
    }
}

if (!function_exists('str_limit')) {
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }
        return rtrim(mb_substr($value, 0, $limit)) . $end;
    }
}

/*
|--------------------------------------------------------------------------
| View template directives (used inside plain-PHP view files)
|--------------------------------------------------------------------------
*/

if (!function_exists('layout')) {
    function layout(string $name): void
    {
        View::$current?->extend($name);
    }
}

if (!function_exists('section')) {
    function section(string $name, ?string $value = null): void
    {
        if ($value !== null) {
            View::$current?->startSection($name, $value);
        } else {
            View::$current?->startSection($name);
        }
    }
}

if (!function_exists('endsection')) {
    function endsection(): void
    {
        View::$current?->stopSection();
    }
}

if (!function_exists('section_yield')) {
    function section_yield(string $name, string $default = ''): string
    {
        return View::$current?->yieldSection($name, $default) ?? $default;
    }
}

if (!function_exists('push')) {
    function push(string $name): void
    {
        View::$current?->startPush($name);
    }
}

if (!function_exists('endpush')) {
    function endpush(): void
    {
        View::$current?->stopPush();
    }
}

if (!function_exists('stack')) {
    function stack(string $name): string
    {
        return View::$current?->yieldStack($name) ?? '';
    }
}
