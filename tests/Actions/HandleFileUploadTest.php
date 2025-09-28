<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Actions\HandleFileUpload;

beforeEach(function () {
    Storage::fake('local');
});

it('can handle file upload and create attachment record', function () {
    $file = UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf');

    $handleFileUpload = new HandleFileUpload();
    $attachment = $handleFileUpload($file);

    expect($attachment)->toBeInstanceOf(\Kirschbaum\Commentions\CommentAttachment::class);
    expect($attachment->original_name)->toBe('test.pdf');
    expect($attachment->file_size)->toBe(1000);
    expect($attachment->mime_type)->toBe('application/pdf');
    expect($attachment->disk)->toBe('local');
    expect($attachment->filename)->toContain(date('Y-m-d_H-i-s'));
    expect($attachment->file_path)->toStartWith('commentions/attachments/');
});

it('stores file in correct directory structure', function () {
    $file = UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf');

    $handleFileUpload = new HandleFileUpload();
    $attachment = $handleFileUpload($file);

    $year = date('Y');
    $month = date('m');

    expect($attachment->file_path)->toBe("commentions/attachments/{$year}/{$month}/{$attachment->filename}");

    expect(Storage::disk('local')->exists($attachment->file_path))->toBeTrue();
});

it('generates unique filenames', function () {
    $file1 = UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf');
    $file2 = UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf');

    $handleFileUpload = new HandleFileUpload();
    $attachment1 = $handleFileUpload($file1);
    $attachment2 = $handleFileUpload($file2);

    expect($attachment1->filename)->not->toBe($attachment2->filename);
    expect($attachment1->file_path)->not->toBe($attachment2->file_path);
});

it('includes metadata in attachment record', function () {
    $file = UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf');

    $handleFileUpload = new HandleFileUpload();
    $attachment = $handleFileUpload($file);

    expect($attachment->metadata)->toBeArray();
    expect($attachment->metadata['extension'])->toBe('pdf');
    expect($attachment->metadata['uploaded_at'])->toBeString();
});

it('handles different file types', function () {
    $pdfFile = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');
    $imageFile = UploadedFile::fake()->image('image.jpg', 100, 100);
    $textFile = UploadedFile::fake()->create('document.txt', 500, 'text/plain');

    $handleFileUpload = new HandleFileUpload();

    $pdfAttachment = $handleFileUpload($pdfFile);
    $imageAttachment = $handleFileUpload($imageFile);
    $textAttachment = $handleFileUpload($textFile);

    expect($pdfAttachment->mime_type)->toBe('application/pdf');
    expect($imageAttachment->mime_type)->toBe('image/jpeg');
    expect($textAttachment->mime_type)->toBe('text/plain');

    expect($pdfAttachment->metadata['extension'])->toBe('pdf');
    expect($imageAttachment->metadata['extension'])->toBe('jpg');
    expect($textAttachment->metadata['extension'])->toBe('txt');
});

it('can use custom disk', function () {
    Storage::fake('s3');

    $file = UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf');

    $handleFileUpload = new HandleFileUpload();
    $attachment = $handleFileUpload($file, 's3');

    expect($attachment->disk)->toBe('s3');
    expect(Storage::disk('s3')->exists($attachment->file_path))->toBeTrue();
});
