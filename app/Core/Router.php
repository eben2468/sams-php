<?php

namespace App\Core;

use App\Http\Middleware\Middleware;
use ReflectionMethod;

class Router
{
    public static ?Router $instance = null;

    /** @var array<int, array> */
    protected array $routes = [];
    /** @var array<string, string> name => uri template */
    protected array $named = [];

    protected array $groupStack = [];

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance(): Router
    {
        return self::$instance ??= new self();
    }

    // --- Registration -----------------------------------------------------

    public function get(string $uri, $action, ?string $name = null, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $action, $name, $middleware);
    }

    public function post(string $uri, $action, ?string $name = null, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $action, $name, $middleware);
    }

    public function put(string $uri, $action, ?string $name = null, array $middleware = []): void
    {
        $this->addRoute('PUT', $uri, $action, $name, $middleware);
    }

    public function delete(string $uri, $action, ?string $name = null, array $middleware = []): void
    {
        $this->addRoute('DELETE', $uri, $action, $name, $middleware);
    }

    public function patch(string $uri, $action, ?string $name = null, array $middleware = []): void
    {
        $this->addRoute('PATCH', $uri, $action, $name, $middleware);
    }

    /**
     * @param array{prefix?:string, middleware?:array, csrf?:bool} $attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    /**
     * Register the 7 RESTful resource routes.
     */
    public function resource(string $name, string $controller, array $options = []): void
    {
        $only = $options['only'] ?? null;
        $except = $options['except'] ?? [];

        $actions = [
            'index'   => ['GET', "/{$name}"],
            'create'  => ['GET', "/{$name}/create"],
            'store'   => ['POST', "/{$name}"],
            'show'    => ['GET', "/{$name}/{id}"],
            'edit'    => ['GET', "/{$name}/{id}/edit"],
            'update'  => ['PUT', "/{$name}/{id}"],
            'destroy' => ['DELETE', "/{$name}/{id}"],
        ];

        foreach ($actions as $action => [$method, $uri]) {
            if ($only !== null && !in_array($action, $only, true)) {
                continue;
            }
            if (in_array($action, $except, true)) {
                continue;
            }
            $this->addRoute($method, $uri, [$controller, $action], "{$name}.{$action}");
        }
    }

    protected function addRoute(string $method, string $uri, $action, ?string $name, array $middleware = []): void
    {
        $prefix = '';
        $groupMiddleware = [];
        $csrf = true;

        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'] ?? '';
            $groupMiddleware = array_merge($groupMiddleware, $group['middleware'] ?? []);
            if (array_key_exists('csrf', $group)) {
                $csrf = $group['csrf'];
            }
        }

        $uri = '/' . trim($prefix . $uri, '/');
        $uri = $uri === '/' ? '/' : rtrim($uri, '/');

        $this->routes[] = [
            'method'     => $method,
            'uri'        => $uri,
            'pattern'    => $this->compilePattern($uri),
            'action'     => $action,
            'name'       => $name,
            'middleware' => array_merge($groupMiddleware, $middleware),
            'csrf'       => $csrf,
        ];

        if ($name !== null) {
            $this->named[$name] = $uri;
        }
    }

    protected function compilePattern(string $uri): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    // --- URL generation ---------------------------------------------------

    public function route(string $name, $parameters = []): string
    {
        if (!isset($this->named[$name])) {
            throw new \RuntimeException("Route [{$name}] not defined.");
        }
        $uri = $this->named[$name];
        $parameters = is_array($parameters) ? $parameters : ['__scalar__' => $parameters];

        // Replace named placeholders.
        $uri = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($m) use (&$parameters) {
            $key = $m[1];
            if (array_key_exists($key, $parameters)) {
                $value = $parameters[$key];
                unset($parameters[$key]);
                return rawurlencode((string) $value);
            }
            if (array_key_exists('__scalar__', $parameters)) {
                $value = $parameters['__scalar__'];
                unset($parameters['__scalar__']);
                return rawurlencode((string) $value);
            }
            return $m[0];
        }, $uri);

        $url = url($uri);

        // Remaining parameters become a query string.
        if ($parameters !== []) {
            $url .= '?' . http_build_query($parameters);
        }
        return $url;
    }

    public function hasRoute(string $name): bool
    {
        return isset($this->named[$name]);
    }

    // --- Dispatch ---------------------------------------------------------

    public function dispatch(Request $request): Response
    {
        try {
            return $this->resolve($request);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors,
                ], 422);
            }
            return (new RedirectResponse($_SERVER['HTTP_REFERER'] ?? url('/')))
                ->withErrors($e->errors)
                ->withInput($request->all());
        } catch (HttpException $e) {
            return $this->errorResponse($request, $e->statusCode, $e->getMessage());
        }
    }

    protected function resolve(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        $matched = null;
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                $allowedMethods[] = $route['method'];
                if ($route['method'] === $method) {
                    $matched = $route;
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    break;
                }
            }
        }

        if ($matched === null) {
            $code = $allowedMethods !== [] ? 405 : 404;
            return $this->errorResponse($request, $code, $code === 405 ? 'Method not allowed.' : 'Page not found.');
        }

        $request->setRouteName($matched['name']);

        // CSRF protection for state-changing web requests.
        if ($matched['csrf'] && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if (!$this->tokensMatch($request)) {
                return $this->errorResponse($request, 419, 'Page expired. Please refresh and try again.');
            }
        }

        // Middleware.
        foreach ($matched['middleware'] as $middleware) {
            $result = Middleware::run($middleware, $request);
            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->callAction($matched['action'], $request, $params ?? []);
    }

    protected function callAction($action, Request $request, array $params): Response
    {
        if (is_callable($action) && !is_array($action)) {
            $result = $action($request, ...array_values($params));
            return $this->toResponse($result);
        }

        [$class, $methodName] = $action;
        $controller = new $class();

        $args = $this->resolveArguments($class, $methodName, $request, $params);
        $result = $controller->{$methodName}(...$args);

        return $this->toResponse($result);
    }

    protected function resolveArguments(string $class, string $method, Request $request, array $params): array
    {
        $reflection = new ReflectionMethod($class, $method);
        $args = [];
        $positional = array_values($params);
        $i = 0;

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type !== null && !$type->isBuiltin() && is_a($type->getName(), Request::class, true)) {
                $args[] = $request;
            } else {
                $args[] = $positional[$i] ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
                $i++;
            }
        }
        return $args;
    }

    protected function toResponse($result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }
        if (is_array($result) || $result instanceof \JsonSerializable) {
            return new JsonResponse($result);
        }
        return new Response((string) $result);
    }

    protected function tokensMatch(Request $request): bool
    {
        $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');
        return is_string($token) && hash_equals(Session::token(), $token);
    }

    protected function errorResponse(Request $request, int $code, string $message): Response
    {
        if ($request->expectsJson()) {
            return new JsonResponse(['success' => false, 'message' => $message], $code);
        }
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . $code . '</title>'
            . '<style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f1f5f9;color:#334155}'
            . '.box{text-align:center}h1{font-size:5rem;margin:0;color:#4338ca}p{font-size:1.1rem}</style></head>'
            . '<body><div class="box"><h1>' . $code . '</h1><p>' . e($message) . '</p>'
            . '<p><a href="' . url('/') . '" style="color:#4338ca">Go home</a></p></div></body></html>';
        return new Response($html, $code);
    }
}
