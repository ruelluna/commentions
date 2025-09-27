<?php

namespace Kirschbaum\Commentions\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileUploadRule implements ValidationRule
{
    public function __construct(
        private array $allowedTypes = [],
        private int $maxSize = 10240, // KB
        private int $maxFiles = 5
    ) {
        $this->allowedTypes = $allowedTypes ?: config('commentions.uploads.allowed_types', []);
        $this->maxSize = $maxSize ?: config('commentions.uploads.max_file_size', 10240);
        $this->maxFiles = $maxFiles ?: config('commentions.uploads.max_files', 5);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail('The :attribute must be an array of files.');
            return;
        }

        // Check number of files
        if (count($value) > $this->maxFiles) {
            $fail("You can upload a maximum of {$this->maxFiles} files.");
            return;
        }

        foreach ($value as $index => $file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }

            // Check file size
            if ($file->getSize() > ($this->maxSize * 1024)) {
                $fail("File #{$index} exceeds the maximum size of " . $this->formatBytes($this->maxSize * 1024) . ".");
                continue;
            }

            // Check file type
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $this->allowedTypes)) {
                $fail("File #{$index} has an invalid file type. Allowed types: " . implode(', ', $this->allowedTypes) . ".");
                continue;
            }

            // Check MIME type for security
            $mimeType = $file->getMimeType();
            if (!$this->isAllowedMimeType($mimeType, $extension)) {
                $fail("File #{$index} has an invalid MIME type.");
                continue;
            }
        }
    }

    private function isAllowedMimeType(string $mimeType, string $extension): bool
    {
        $allowedMimeTypes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'txt' => ['text/plain'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
        ];

        return isset($allowedMimeTypes[$extension]) &&
               in_array($mimeType, $allowedMimeTypes[$extension]);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
