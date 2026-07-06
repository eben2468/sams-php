<?php

namespace App\Core;

class Response
{
    public function __construct(
        protected string $content = '',
        protected int $status = 200,
        protected array $headers = []
    ) {
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
        echo $this->content;
    }
}
