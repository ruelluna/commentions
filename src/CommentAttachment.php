<?php

namespace Kirschbaum\Commentions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CommentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'filename',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'disk',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Config::getCommentModel());
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->file_path);
    }

    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    public function getFileIcon(): string
    {
        if ($this->isImage()) {
            return 'heroicon-o-photo';
        }

        if ($this->isPdf()) {
            return 'heroicon-o-document-text';
        }

        if ($this->isVideo()) {
            return 'heroicon-o-video-camera';
        }

        if ($this->isAudio()) {
            return 'heroicon-o-musical-note';
        }

        return 'heroicon-o-document';
    }

    public function delete(): bool
    {
        // Delete the file from storage
        if (Storage::disk($this->disk)->exists($this->file_path)) {
            Storage::disk($this->disk)->delete($this->file_path);
        }

        return parent::delete();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            // Ensure file is deleted when model is deleted
            if (Storage::disk($attachment->disk)->exists($attachment->file_path)) {
                Storage::disk($attachment->disk)->delete($attachment->file_path);
            }
        });
    }
}
