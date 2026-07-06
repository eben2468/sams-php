<?php

namespace App\Core;

use App\Models\User;

/**
 * Returned by the auth() helper so views/controllers can call
 * auth()->user(), auth()->check(), auth()->id(), auth()->guest().
 */
class AuthProxy
{
    public function user(): ?User
    {
        return Auth::user();
    }

    public function check(): bool
    {
        return Auth::check();
    }

    public function guest(): bool
    {
        return Auth::guest();
    }

    public function id()
    {
        return Auth::id();
    }
}
