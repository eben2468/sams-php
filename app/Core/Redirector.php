<?php

namespace App\Core;

class Redirector
{
    public function to(string $path): RedirectResponse
    {
        if (!preg_match('#^https?://#', $path)) {
            $path = url($path);
        }
        return new RedirectResponse($path);
    }

    public function route(string $name, array $parameters = []): RedirectResponse
    {
        return new RedirectResponse(route($name, $parameters));
    }

    public function back(): RedirectResponse
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        return new RedirectResponse($referer);
    }

    public function intended(string $default = '/'): RedirectResponse
    {
        // Both the stored intended URL (a raw REQUEST_URI) and the default
        // (typically route('...')) are already absolute root-relative paths
        // that include the app's base prefix — do NOT re-prefix them.
        $url = Session::get('url_intended', $default);
        Session::forget('url_intended');
        return new RedirectResponse($url);
    }
}
