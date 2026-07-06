<?php

namespace App\Core;

class Hash
{
    public static function make(string $value): string
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    public static function check(string $value, ?string $hashed): bool
    {
        if ($hashed === null || $hashed === '') {
            return false;
        }
        return password_verify($value, $hashed);
    }
}
