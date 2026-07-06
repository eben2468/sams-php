<?php

namespace App\Core;

/**
 * Wraps a single $_FILES entry. Mirrors the handful of methods the
 * controllers use (store, getRealPath, getClientOriginalName, etc.).
 */
class UploadedFile
{
    public function __construct(protected array $file)
    {
    }

    public function isValid(): bool
    {
        return ($this->file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
            && ($this->file['tmp_name'] ?? '') !== '';
    }

    public function getRealPath(): string
    {
        return $this->file['tmp_name'] ?? '';
    }

    public function getClientOriginalName(): string
    {
        return $this->file['name'] ?? '';
    }

    public function getClientOriginalExtension(): string
    {
        return strtolower(pathinfo($this->file['name'] ?? '', PATHINFO_EXTENSION));
    }

    /**
     * Store the file under public/uploads/{dir} and return the relative
     * path (e.g. "students/abc123.jpg").
     */
    public function store(string $dir): string
    {
        $base = dirname(__DIR__, 2) . '/public/uploads/' . trim($dir, '/');
        if (!is_dir($base)) {
            mkdir($base, 0775, true);
        }

        $ext = $this->getClientOriginalExtension() ?: 'bin';
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $base . '/' . $filename;

        if (is_uploaded_file($this->file['tmp_name'])) {
            move_uploaded_file($this->file['tmp_name'], $destination);
        } else {
            // Fallback for CLI/testing contexts.
            copy($this->file['tmp_name'], $destination);
        }

        return trim($dir, '/') . '/' . $filename;
    }
}
