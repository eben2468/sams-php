<?php

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;

/**
 * Streams files stored under public/uploads through the PHP front controller.
 * This is a universal fallback for hosts that do not serve public/uploads as
 * static files (so logos and student photos still display everywhere).
 */
class MediaController extends Controller
{
    /** Extensions we are willing to serve, mapped to their MIME type. */
    private const MIME = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'bmp'  => 'image/bmp',
        'svg'  => 'image/svg+xml',
    ];

    public function show(Request $request): Response
    {
        $relative = (string) $request->input('f', '');
        $relative = str_replace('\\', '/', $relative);

        // Reject empty paths, null bytes and any traversal attempt.
        if ($relative === '' || str_contains($relative, "\0") || str_contains($relative, '..')) {
            return $this->notFound();
        }

        $baseDir = dirname(__DIR__, 3) . '/public/uploads';
        $baseReal = realpath($baseDir);
        $target = realpath($baseDir . '/' . ltrim($relative, '/'));

        // The resolved file must exist and stay inside the uploads directory.
        if ($baseReal === false || $target === false || !is_file($target)
            || strncmp($target, $baseReal . DIRECTORY_SEPARATOR, strlen($baseReal) + 1) !== 0) {
            return $this->notFound();
        }

        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        if (!isset(self::MIME[$ext])) {
            return $this->notFound();
        }

        return new Response((string) file_get_contents($target), 200, [
            'Content-Type'   => self::MIME[$ext],
            'Content-Length' => (string) filesize($target),
            'Cache-Control'  => 'public, max-age=86400',
        ]);
    }

    private function notFound(): Response
    {
        return new Response('Not found', 404, ['Content-Type' => 'text/plain']);
    }
}
