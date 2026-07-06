<?php

namespace App\Core;

use RuntimeException;

class HttpException extends RuntimeException
{
    public int $statusCode;

    public function __construct(int $statusCode, string $message = '')
    {
        $this->statusCode = $statusCode;
        parent::__construct($message);
    }
}
