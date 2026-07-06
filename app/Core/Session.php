<?php

namespace App\Core;

/**
 * Thin wrapper around native PHP sessions, with flash-data support
 * (success/error messages, validation errors, old input).
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require dirname(__DIR__, 2) . '/config/config.php';
            session_name($config['session']['name']);
            session_set_cookie_params([
                'lifetime' => $config['session']['lifetime'] * 60,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        // Age flash data: move "new" flash into current request, clear old.
        if (isset($_SESSION['_flash']['old'])) {
            unset($_SESSION['_flash']['old']);
        }
        if (isset($_SESSION['_flash']['new'])) {
            $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'];
            unset($_SESSION['_flash']['new']);
        }
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, $value): void
    {
        $_SESSION['_flash']['new'][$key] = $value;
    }

    public static function getFlash(string $key, $default = null)
    {
        return $_SESSION['_flash']['old'][$key] ?? $default;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash']['old'][$key]);
    }

    public static function flashInput(array $input): void
    {
        $_SESSION['_flash']['new']['_old_input'] = $input;
    }

    public static function oldInput(): array
    {
        return $_SESSION['_flash']['old']['_old_input'] ?? [];
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function invalidate(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function token(): string
    {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
}
