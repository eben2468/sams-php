<?php

namespace App\Core;

use App\Models\User;

/**
 * HTTP request abstraction over PHP superglobals.
 */
class Request
{
    protected array $query;
    protected array $request;
    protected array $json = [];
    protected array $files;
    protected array $server;
    protected ?string $routeName = null;

    public function __construct(array $query, array $request, array $files, array $server)
    {
        $this->query = $query;
        $this->request = $request;
        $this->files = $files;
        $this->server = $server;

        $contentType = $server['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->json = $decoded;
            }
        }
    }

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER);
    }

    // --- Input access -----------------------------------------------------

    public function all(): array
    {
        return array_merge($this->query, $this->request, $this->json);
    }

    public function input(string $key, $default = null)
    {
        return $this->all()[$key] ?? $default;
    }

    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }

    public function boolean(string $key): bool
    {
        return filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN);
    }

    public function __get($key)
    {
        return $this->input($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    // --- Files ------------------------------------------------------------

    public function hasFile(string $key): bool
    {
        $file = $this->files[$key] ?? null;
        return is_array($file)
            && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
            && ($file['tmp_name'] ?? '') !== '';
    }

    public function file(string $key): ?UploadedFile
    {
        if (!isset($this->files[$key])) {
            return null;
        }
        return new UploadedFile($this->files[$key]);
    }

    // --- Validation -------------------------------------------------------

    public function validate(array $rules): array
    {
        try {
            return Validator::validate($this->all(), $rules, $this->files);
        } catch (ValidationException $e) {
            // Flash errors + old input for web requests; rethrow for the
            // router to convert to the appropriate response.
            throw $e;
        }
    }

    // --- Metadata ---------------------------------------------------------

    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST') {
            $spoof = strtoupper((string) ($this->request['_method'] ?? $this->json['_method'] ?? ''));
            if (in_array($spoof, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoof;
            }
        }
        return $method;
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Strip the base path the app is mounted under so routes can be
        // declared as "/", "/login", etc. regardless of subdirectory.
        $base = base_uri();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $path = '/' . trim($path, '/');
        return $path;
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function ip(): ?string
    {
        return $this->server['REMOTE_ADDR'] ?? null;
    }

    public function userAgent(): ?string
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    public function header(string $key, $default = null)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }

    public function expectsJson(): bool
    {
        $accept = $this->server['HTTP_ACCEPT'] ?? '';
        $requestedWith = $this->server['HTTP_X_REQUESTED_WITH'] ?? '';
        $contentType = $this->server['CONTENT_TYPE'] ?? '';

        return str_contains($accept, 'application/json')
            || strtolower($requestedWith) === 'xmlhttprequest'
            || str_contains($contentType, 'application/json');
    }

    public function wantsJson(): bool
    {
        return $this->expectsJson();
    }

    // --- Routing / auth ---------------------------------------------------

    public function setRouteName(?string $name): void
    {
        $this->routeName = $name;
    }

    public function routeName(): ?string
    {
        return $this->routeName;
    }

    public function routeIs(string $pattern): bool
    {
        if ($this->routeName === null) {
            return false;
        }
        $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';
        return (bool) preg_match($regex, $this->routeName);
    }

    public function user(): ?User
    {
        return Auth::user();
    }

    public function session(): SessionProxy
    {
        return new SessionProxy();
    }
}
