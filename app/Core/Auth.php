<?php

namespace App\Core;

use App\Models\User;

/**
 * Session-based authentication. Replaces Laravel's Auth facade + Sanctum.
 */
class Auth
{
    protected static ?User $user = null;
    protected static bool $resolved = false;

    public static function login(User $user, bool $remember = false): void
    {
        Session::put('auth_user_id', $user->getKey());
        Session::regenerate();
        self::$user = $user;
        self::$resolved = true;
    }

    public static function logout(): void
    {
        Session::forget('auth_user_id');
        self::$user = null;
        self::$resolved = true;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function user(): ?User
    {
        if (self::$resolved) {
            return self::$user;
        }
        self::$resolved = true;

        $id = Session::get('auth_user_id');
        if ($id === null) {
            return self::$user = null;
        }

        $user = User::find($id);
        if ($user === null || !$user->is_active) {
            Session::forget('auth_user_id');
            return self::$user = null;
        }

        return self::$user = $user;
    }

    public static function id()
    {
        $user = self::user();
        return $user?->getKey();
    }
}
