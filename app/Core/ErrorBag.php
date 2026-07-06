<?php

namespace App\Core;

/**
 * Holds validation errors for display in views ($errors->...).
 */
class ErrorBag
{
    public function __construct(protected array $errors = [])
    {
    }

    public function any(): bool
    {
        return $this->errors !== [];
    }

    public function has(string $key): bool
    {
        return isset($this->errors[$key]) && $this->errors[$key] !== [];
    }

    public function first(string $key): ?string
    {
        return $this->errors[$key][0] ?? null;
    }

    public function get(string $key): array
    {
        return $this->errors[$key] ?? [];
    }

    public function all(): array
    {
        $all = [];
        foreach ($this->errors as $messages) {
            foreach ($messages as $message) {
                $all[] = $message;
            }
        }
        return $all;
    }
}
