<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use ZipArchive;

/**
 * The uploaded file's detected content type must match its extension:
 * .pdf => application/pdf, .epub => application/epub+zip (or a zip whose
 * `mimetype` entry says application/epub+zip, for older libmagic builds).
 */
class EbookFile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $mime = $value->getMimeType();

        $ok = match ($extension) {
            'pdf' => $mime === 'application/pdf',
            'epub' => $mime === 'application/epub+zip'
                || ($mime === 'application/zip' && $this->isEpubArchive($value->getRealPath())),
            default => false,
        };

        if (! $ok) {
            $fail('The :attribute must be a valid PDF or EPUB file.');
        }
    }

    private function isEpubArchive(string|false $path): bool
    {
        if ($path === false || ! class_exists(ZipArchive::class)) {
            return false;
        }

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return false;
        }

        $mimetype = $zip->getFromName('mimetype');
        $zip->close();

        return is_string($mimetype) && trim($mimetype) === 'application/epub+zip';
    }
}
