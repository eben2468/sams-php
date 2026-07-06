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
        $bytes = (string) $row['data'];

        // Trim the transparent (or uniform-colour) border baked into the source
        // image so the emblem fills the icon without any zooming/cropping of the
        // artwork itself. Falls back to the original if GD isn't available.
        $trimmed = $this->trimBorder($bytes);
        if ($trimmed !== null) {
            $mime  = 'image/png';
            $bytes = $trimmed;
        }

        $href = 'data:' . $mime . ';base64,' . base64_encode($bytes);

        // Fill the whole icon with the (trimmed) logo — 100%, no letterboxing.
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 64 64">'
             . '<image x="0" y="0" width="64" height="64" preserveAspectRatio="xMidYMid slice" '
             . 'href="' . $href . '" xlink:href="' . $href . '"/>'
             . '</svg>';

        return new Response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Crop away the transparent (or uniform-colour) padding around a logo and
     * return the trimmed PNG bytes. Returns null if GD is unavailable or nothing
     * could be trimmed.
     */
    private function trimBorder(string $bytes): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $img = @imagecreatefromstring($bytes);
        if (!$img) {
            return null;
        }
        imagesavealpha($img, true);

        $w = imagesx($img);
        $h = imagesy($img);

        // Find the bounding box of visible (non-transparent) pixels. In GD alpha
        // runs 0 (opaque) .. 127 (fully transparent); treat < 100 as content.
        $minx = $w; $miny = $h; $maxx = -1; $maxy = -1;
        $step = max(1, (int) floor(min($w, $h) / 500));
        for ($y = 0; $y < $h; $y += $step) {
            for ($x = 0; $x < $w; $x += $step) {
                $a = (imagecolorat($img, $x, $y) >> 24) & 0x7F;
                if ($a < 100) {
                    if ($x < $minx) $minx = $x;
                    if ($x > $maxx) $maxx = $x;
                    if ($y < $miny) $miny = $y;
                    if ($y > $maxy) $maxy = $y;
                }
            }
        }

        // Fully transparent image, or the artwork already fills the canvas.
        if ($maxx < 0) { imagedestroy($img); return null; }

        // Small safety margin (covers the sampling step) then clamp.
        $pad  = $step + (int) round(min($w, $h) * 0.01);
        $minx = max(0, $minx - $pad);
        $miny = max(0, $miny - $pad);
        $maxx = min($w - 1, $maxx + $pad);
        $maxy = min($h - 1, $maxy + $pad);
        $cw = $maxx - $minx + 1;
        $ch = $maxy - $miny + 1;
        if ($cw >= $w && $ch >= $h) { imagedestroy($img); return null; }

        $dst = imagecreatetruecolor($cw, $ch);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopy($dst, $img, 0, 0, $minx, $miny, $cw, $ch);

        ob_start();
        imagepng($dst);
        $out = ob_get_clean();

        imagedestroy($img);
        imagedestroy($dst);

        return $out !== '' ? $out : null;
    }

    private function notFound(): Response
    {
        return new Response('Not found', 404, ['Content-Type' => 'text/plain']);
    }
}
