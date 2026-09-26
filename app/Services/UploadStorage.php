<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores admin uploads under random names: images on the public media disk,
 * ebooks on the private disk (never web-accessible).
 */
class UploadStorage
{
    /**
     * @return array{file_path: string, file_format: string, file_size: int}
     */
    public function storeEbook(UploadedFile $file): array
    {
        $format = strtolower($file->getClientOriginalExtension()) === 'epub' ? 'epub' : 'pdf';

        $path = $file->storeAs('ebooks', Str::random(40).'.'.$format, [
            'disk' => config('bookplanet.ebook_disk'),
        ]);

        return [
            'file_path' => $path,
            'file_format' => $format,
            'file_size' => (int) $file->getSize(),
        ];
    }

    public function storeImage(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, ['disk' => config('bookplanet.media_disk')]);
    }

    public function deleteEbook(?string $path): void
    {
        if ($path) {
            Storage::disk(config('bookplanet.ebook_disk'))->delete($path);
        }
    }

    public function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk(config('bookplanet.media_disk'))->delete($path);
        }
    }
}
