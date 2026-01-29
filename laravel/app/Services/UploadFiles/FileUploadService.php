<?php

namespace App\Services\UploadFiles;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * Загружает один файл и удаляет старый, если указан.
     */
    public function uploadFile(?UploadedFile $file, string $disk = 'public', string $directory = 'uploads', ?string $oldPath = null): ?string
    {
        if (!$file) {
            return null;
        }

        // Удаляем старый файл
        if ($oldPath) {
            Storage::disk($disk)->delete($oldPath);
        }

        // Сохраняем новый
        return $file->store($directory, $disk);
    }

    /**
     * Загружает несколько файлов.
     */
    public function uploadMultipleFiles(array $files, string $disk = 'public', string $directory = 'uploads'): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store($directory, $disk);
            }
        }
        return $paths;
    }

    /**
     * Удаляет файл.
     */
    public function deleteFile(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->delete($path);
    }
}
