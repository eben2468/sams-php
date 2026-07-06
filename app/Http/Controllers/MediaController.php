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

    /**
     * Serve the branding logo stored in the database (app_files.name = 'logo').
     * Works regardless of filesystem writability or static serving on the host.
     */
    public function logo(): Response
    {
        try {
            $row = \App\Core\Database::selectOne("SELECT `mime`, `data` FROM `app_files` WHERE `name` = 'logo'");
        } catch (\Throwable $e) {
            $row = null;
        }

        if (!$row || ($row['data'] ?? '') === '') {
            return $this->notFound();
        }

        return new Response((string) $row['data'], 200, [
            'Content-Type'   => $row['mime'] ?: 'image/png',
            'Content-Length' => (string) strlen((string) $row['data']),
            'Cache-Control'  => 'public, max-age=86400',
        ]);
    }

    /**
     * Serve a user's avatar stored in the database (app_files.name = avatar-{id}).
     */
    public function avatar($id): Response
    {
        $id = (int) $id;
        try {
            $row = \App\Core\Database::selectOne(
                "SELECT `mime`, `data` FROM `app_files` WHERE `name` = ?",
                ['avatar-' . $id]
            );
        } catch (\Throwable $e) {
            $row = null;
        }

        if (!$row || ($row['data'] ?? '') === '') {
            return $this->notFound();
        }

        return new Response((string) $row['data'], 200, [
            'Content-Type'   => $row['mime'] ?: 'image/png',
            'Content-Length' => (string) strlen((string) $row['data']),
            'Cache-Control'  => 'public, max-age=86400',
        ]);
    }

    /**
     * Favicon built from the branding logo. Wraps the logo image in an SVG that
     * scales it to FILL the icon area (cover), so it appears large in the tab
     * instead of small with padding.
     */
    public function favicon(): Response
    {
        try {
            $row = \App\Core\Database::selectOne("SELECT `mime`, `data` FROM `app_files` WHERE `name` = 'logo'");
        } catch (\Throwable $e) {
            $row = null;
        }

        if (!$row || ($row['data'] ?? '') === '') {
            return $this->notFound();
        }

        $mime = $row['mime'] ?: 'image/png';
        $b64  = base64_encode((string) $row['data']);
        $href = 'data:' . $mime . ';base64,' . $b64;

        // Show the whole logo (contain, no cropping) centred in the icon.
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 64 64">'
             . '<image x="0" y="0" width="64" height="64" preserveAspectRatio="xMidYMid meet" '
             . 'href="' . $href . '" xlink:href="' . $href . '"/>'
             . '</svg>';

        return new Response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function notFound(): Response
    {
        return new Response('Not found', 404, ['Content-Type' => 'text/plain']);
    }
}
