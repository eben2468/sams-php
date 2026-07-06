<?php

namespace App\Core;

/**
 * Minimal template engine for plain-PHP views with layout inheritance
 * (layout/section/yield) and stacks (push/stack). Replaces Blade.
 */
class View
{
    public static ?View $current = null;
    protected static string $viewPath = '';

    public ?string $layout = null;
    protected array $sections = [];
    protected array $sectionStack = [];
    protected array $stacks = [];
    protected array $pushStack = [];

    public static function setViewPath(string $path): void
    {
        self::$viewPath = rtrim($path, '/');
    }

    /**
     * Render a view (with optional layout) and return the HTML string.
     */
    public static function render(string $name, array $data = []): string
    {
        $engine = new self();
        $previous = self::$current;
        self::$current = $engine;

        $content = $engine->renderFile($name, $data);

        if ($engine->layout !== null) {
            $layout = $engine->layout;
            if (!isset($engine->sections['content'])) {
                $engine->sections['content'] = $content;
            }
            $content = $engine->renderFile($layout, $data);
        }

        self::$current = $previous;
        return $content;
    }

    protected function renderFile(string $name, array $data): string
    {
        $file = self::$viewPath . '/' . str_replace('.', '/', $name) . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View [{$name}] not found at {$file}");
        }

        $data['errors'] = $data['errors'] ?? new ErrorBag(Session::getFlash('errors', []));

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    // --- Template directives ---------------------------------------------

    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }

    public function startSection(string $name, ?string $value = null): void
    {
        if ($value !== null) {
            $this->sections[$name] = $value;
            return;
        }
        $this->sectionStack[] = $name;
        ob_start();
    }

    public function stopSection(): void
    {
        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
    }

    public function yieldSection(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function startPush(string $name): void
    {
        $this->pushStack[] = $name;
        ob_start();
    }

    public function stopPush(): void
    {
        $name = array_pop($this->pushStack);
        $this->stacks[$name] = ($this->stacks[$name] ?? '') . ob_get_clean();
    }

    public function yieldStack(string $name): string
    {
        return $this->stacks[$name] ?? '';
    }
}
