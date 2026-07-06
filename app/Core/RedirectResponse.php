<?php

namespace App\Core;

class RedirectResponse extends Response
{
    public function __construct(protected string $url, int $status = 302)
    {
        parent::__construct('', $status, ['Location' => $url]);
    }

    public function with(string $key, $value): self
    {
        Session::flash($key, $value);
        return $this;
    }

    public function withErrors(array $errors): self
    {
        Session::flash('errors', $errors);
        return $this;
    }

    public function withInput(array $input = []): self
    {
        Session::flashInput($input);
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Location: ' . $this->url);
    }
}
