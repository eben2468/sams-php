<?php

namespace App\Core;

class ResponseFactory
{
    public function json($data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    public function make(string $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Build a file-download response from in-memory content.
     */
    public function download(string $content, string $filename, string $contentType = 'application/octet-stream'): Response
    {
        return new Response($content, 200, [
            'Content-Type'        => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => (string) strlen($content),
        ]);
    }
}
