<?php

namespace App\Core;

use RuntimeException;

class ValidationException extends RuntimeException
{
    /** @var array<string, string[]> */
    public array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('The given data was invalid.');
    }

    public static function withMessages(array $messages): self
    {
        $errors = [];
        foreach ($messages as $field => $msgs) {
            $errors[$field] = is_array($msgs) ? $msgs : [$msgs];
        }
        return new self($errors);
    }
}
