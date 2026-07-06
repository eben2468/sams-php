<?php

namespace App\Core;

/**
 * Object returned by Request::session() so controllers can call
 * ->regenerate(), ->invalidate(), ->regenerateToken() like in Laravel.
 */
class SessionProxy
{
    public function regenerate(): void
    {
        Session::regenerate();
    }

    public function invalidate(): void
    {
        Session::invalidate();
    }

    public function regenerateToken(): void
    {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }

    public function get(string $key, $default = null)
    {
        return Session::get($key, $default);
    }

    public function put(string $key, $value): void
    {
        Session::put($key, $value);
    }

    public function flash(string $key, $value): void
    {
        Session::flash($key, $value);
    }
}
