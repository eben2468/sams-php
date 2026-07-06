<?php

namespace App\Http\Middleware;

use App\Core\Auth;
use App\Core\JsonResponse;
use App\Core\RedirectResponse;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Resolves and executes named middleware. Returns a Response to
 * short-circuit the request, or null to continue.
 */
class Middleware
{
    public static function run(string $name, Request $request): ?Response
    {
        if ($name === 'guest') {
            return self::guest($request);
        }
        if ($name === 'auth') {
            return self::auth($request);
        }
        if (str_starts_with($name, 'role:')) {
            $roles = explode(',', substr($name, 5));
            return self::role($request, $roles);
        }
        return null;
    }

    protected static function guest(Request $request): ?Response
    {
        if (Auth::check()) {
            return new RedirectResponse(route('dashboard'));
        }
        return null;
    }

    protected static function auth(Request $request): ?Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return new JsonResponse(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            Session::put('url_intended', $request->uri());
            return new RedirectResponse(route('login'));
        }
        return null;
    }

    protected static function role(Request $request, array $roles): ?Response
    {
        $user = Auth::user();

        if ($user === null) {
            if ($request->expectsJson()) {
                return new JsonResponse(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            return new RedirectResponse(route('login'));
        }

        if (!$user->hasRole($roles)) {
            if ($request->expectsJson()) {
                return new JsonResponse(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>403</title></head>'
                . '<body style="font-family:system-ui;text-align:center;padding:4rem">'
                . '<h1 style="font-size:4rem;color:#dc2626">403</h1><p>Unauthorized action.</p>'
                . '<p><a href="' . route('dashboard') . '">Back to dashboard</a></p></body></html>';
            return new Response($html, 403);
        }

        return null;
    }
}
