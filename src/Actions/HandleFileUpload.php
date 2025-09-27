<?php

namespace Kirschbaum\Commentions\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kirschbaum\Commentions\CommentAttachment;

class HandleFileUpload
{
    public function __invoke(UploadedFile $file, string $disk = 'local'): CommentAttachment
    {
        $filename = $this->generateFilename($file);
        $path = $this->generatePath($filename);

        // Store the file
        $storedPath = $file->storeAs(
            dirname($path),
            basename($path),
            $disk
        );

        return CommentAttachment::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'disk' => $disk,
            'metadata' => [
                'extension' => $file->getClientOriginalExtension(),
                'uploaded_at' => now()->toISOString(),
            ],
        ]);
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    protected function generatePath(string $filename): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        return "commentions/attachments/{$year}/{$month}/{$filename}";
    }
}
