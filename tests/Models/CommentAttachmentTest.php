<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\CommentAttachment;
use Kirschbaum\Commentions\Tests\Database\Factories\PostFactory;
use Kirschbaum\Commentions\Tests\Database\Factories\UserFactory;

beforeEach(function () {
    Storage::fake('local');
});

it('can create a comment attachment', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();
    $comment = $post->comment('Test comment', $user);

    $file = UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf');

    $attachment = CommentAttachment::create([
        'comment_id' => $comment->id,
        'filename' => 'test.pdf',
        'original_name' => 'test.pdf',
        'file_path' => 'commentions/attachments/2024/01/test.pdf',
        'file_size' => 1000,
        'mime_type' => 'application/pdf',
        'disk' => 'local',
    ]);

    expect($attachment->comment_id)->toBe($comment->id);
    expect($attachment->filename)->toBe('test.pdf');
    expect($attachment->original_name)->toBe('test.pdf');
    expect($attachment->file_size)->toBe(1000);
    expect($attachment->mime_type)->toBe('application/pdf');
});

it('can determine if attachment is an image', function () {
    $attachment = new CommentAttachment([
        'mime_type' => 'image/jpeg',
    ]);

    expect($attachment->isImage())->toBeTrue();

    $attachment = new CommentAttachment([
        'mime_type' => 'application/pdf',
    ]);

    expect($attachment->isImage())->toBeFalse();
});

it('can determine if attachment is a pdf', function () {
    $attachment = new CommentAttachment([
        'mime_type' => 'application/pdf',
    ]);

    expect($attachment->isPdf())->toBeTrue();

    $attachment = new CommentAttachment([
        'mime_type' => 'image/jpeg',
    ]);

    expect($attachment->isPdf())->toBeFalse();
});

it('can format file size in human readable format', function () {
    $attachment = new CommentAttachment([
        'file_size' => 1024,
    ]);

    expect($attachment->human_readable_size)->toBe('1 KB');

    $attachment = new CommentAttachment([
        'file_size' => 1048576, // 1MB
    ]);

    expect($attachment->human_readable_size)->toBe('1 MB');
});

it('can get file icon based on type', function () {
    $imageAttachment = new CommentAttachment([
        'mime_type' => 'image/jpeg',
    ]);

    expect($imageAttachment->getFileIcon())->toBe('heroicon-o-photo');

    $pdfAttachment = new CommentAttachment([
        'mime_type' => 'application/pdf',
    ]);

    expect($pdfAttachment->getFileIcon())->toBe('heroicon-o-document-text');

    $videoAttachment = new CommentAttachment([
        'mime_type' => 'video/mp4',
    ]);

    expect($videoAttachment->getFileIcon())->toBe('heroicon-o-video-camera');

    $audioAttachment = new CommentAttachment([
        'mime_type' => 'audio/mp3',
    ]);

    expect($audioAttachment->getFileIcon())->toBe('heroicon-o-musical-note');

    $documentAttachment = new CommentAttachment([
        'mime_type' => 'text/plain',
    ]);

    expect($documentAttachment->getFileIcon())->toBe('heroicon-o-document');
});

it('deletes file from storage when attachment is deleted', function () {
    Storage::fake('local');

    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();
    $comment = $post->comment('Test comment', $user);

    $filePath = 'commentions/attachments/2024/01/test.pdf';
    Storage::put($filePath, 'test content');

    $attachment = CommentAttachment::create([
        'comment_id' => $comment->id,
        'filename' => 'test.pdf',
        'original_name' => 'test.pdf',
        'file_path' => $filePath,
        'file_size' => 1000,
        'mime_type' => 'application/pdf',
        'disk' => 'local',
    ]);

    expect(Storage::exists($filePath))->toBeTrue();

    $attachment->delete();

    expect(Storage::exists($filePath))->toBeFalse();
});
