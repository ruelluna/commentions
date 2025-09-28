<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Kirschbaum\Commentions\Rules\FileUploadRule;

it('passes validation for valid files', function () {
    $files = [
        UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf'),
        UploadedFile::fake()->image('image.jpg', 100, 100),
        UploadedFile::fake()->create('document.txt', 500, 'text/plain'),
    ];

    $validator = Validator::make(['files' => $files], [
        'files' => [new FileUploadRule()],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails validation for files that are too large', function () {
    $largeFile = UploadedFile::fake()->create('large.pdf', 15000, 'application/pdf'); // 15MB

    $validator = Validator::make(['files' => [$largeFile]], [
        'files' => [new FileUploadRule()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('files'))->toContain('exceeds the maximum size');
});

it('fails validation for invalid file types', function () {
    $invalidFile = UploadedFile::fake()->create('script.exe', 1000, 'application/x-executable');

    $validator = Validator::make(['files' => [$invalidFile]], [
        'files' => [new FileUploadRule()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('files'))->toContain('invalid file type');
});

it('fails validation for too many files', function () {
    $files = collect(range(1, 6))->map(fn () => UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf')
    )->toArray();

    $validator = Validator::make(['files' => $files], [
        'files' => [new FileUploadRule()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('files'))->toContain('maximum of 5 files');
});

it('fails validation for invalid MIME types', function () {
    // Create a file with correct extension but wrong MIME type
    $file = UploadedFile::fake()->create('document.pdf', 1000, 'text/plain');

    $validator = Validator::make(['files' => [$file]], [
        'files' => [new FileUploadRule()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('files'))->toContain('invalid MIME type');
});

it('can use custom validation parameters', function () {
    $files = [
        UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf'),
        UploadedFile::fake()->create('document2.pdf', 1000, 'application/pdf'),
    ];

    // Test with custom max files (1)
    $validator = Validator::make(['files' => $files], [
        'files' => [new FileUploadRule(allowedTypes: ['pdf'], maxSize: 10240, maxFiles: 1)],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('files'))->toContain('maximum of 1 files');
});

it('validates file size correctly', function () {
    $file = UploadedFile::fake()->create('document.pdf', 5000, 'application/pdf'); // 5MB

    // Test with custom max size (2MB)
    $validator = Validator::make(['files' => [$file]], [
        'files' => [new FileUploadRule(allowedTypes: ['pdf'], maxSize: 2048, maxFiles: 5)],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('files'))->toContain('exceeds the maximum size');
});

it('validates allowed file types correctly', function () {
    $file = UploadedFile::fake()->create('document.doc', 1000, 'application/msword');

    // Test with custom allowed types (only PDF)
    $validator = Validator::make(['files' => [$file]], [
        'files' => [new FileUploadRule(allowedTypes: ['pdf'], maxSize: 10240, maxFiles: 5)],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('files'))->toContain('invalid file type');
});

it('handles empty file array', function () {
    $validator = Validator::make(['files' => []], [
        'files' => [new FileUploadRule()],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('handles non-array input', function () {
    $validator = Validator::make(['files' => 'not-an-array'], [
        'files' => [new FileUploadRule()],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('files'))->toContain('must be an array of files');
});
