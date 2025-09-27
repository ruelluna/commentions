<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Livewire\Comments;
use Kirschbaum\Commentions\Tests\Database\Factories\PostFactory;
use Kirschbaum\Commentions\Tests\Database\Factories\UserFactory;
use Kirschbaum\Commentions\Tests\Models\Post;
use Kirschbaum\Commentions\Tests\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

it('can upload files with a comment', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');
    $file2 = UploadedFile::fake()->image('image.jpg', 100, 100);

    Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'This is a test comment with files')
        ->set('attachments', [$file1, $file2])
        ->call('save');

    $comment = $post->comments()->latest()->first();

    expect($comment->body)->toBe('This is a test comment with files');
    expect($comment->attachments)->toHaveCount(2);
    expect($comment->attachments->first()->original_name)->toBe('document.pdf');
    expect($comment->attachments->last()->original_name)->toBe('image.jpg');
});

it('validates file upload rules', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    // Test file size validation
    $largeFile = UploadedFile::fake()->create('large.pdf', 15000, 'application/pdf'); // 15MB

    Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment')
        ->set('attachments', [$largeFile])
        ->call('save')
        ->assertHasErrors(['attachments']);

    // Test file type validation
    $invalidFile = UploadedFile::fake()->create('script.exe', 1000, 'application/x-executable');

    Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment')
        ->set('attachments', [$invalidFile])
        ->call('save')
        ->assertHasErrors(['attachments']);

    // Test max files validation
    $files = collect(range(1, 6))->map(fn() => UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf'));

    Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment')
        ->set('attachments', $files->toArray())
        ->call('save')
        ->assertHasErrors(['attachments']);
});

it('can remove attachments before saving', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    $file1 = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');
    $file2 = UploadedFile::fake()->image('image.jpg', 100, 100);

    $component = Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment')
        ->set('attachments', [$file1, $file2]);

    expect($component->get('attachments'))->toHaveCount(2);

    $component->call('removeAttachment', 0);

    expect($component->get('attachments'))->toHaveCount(1);
    expect($component->get('attachments')[0]->getClientOriginalName())->toBe('image.jpg');
});

it('clears attachments when comment is cleared', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

    $component = Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment')
        ->set('attachments', [$file]);

    expect($component->get('attachments'))->toHaveCount(1);

    $component->call('clear');

    expect($component->get('commentBody'))->toBe('');
    expect($component->get('attachments'))->toHaveCount(0);
});

it('stores files in correct directory structure', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

    Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment')
        ->set('attachments', [$file])
        ->call('save');

    $attachment = $post->comments()->latest()->first()->attachments()->first();

    expect($attachment->file_path)->toStartWith('commentions/attachments/');
    expect($attachment->file_path)->toContain(date('Y'));
    expect($attachment->file_path)->toContain(date('m'));
    expect($attachment->filename)->toContain(date('Y-m-d_H-i-s'));
});

it('handles comment without attachments', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment without files')
        ->set('attachments', [])
        ->call('save');

    $comment = $post->comments()->latest()->first();

    expect($comment->body)->toBe('Test comment without files');
    expect($comment->attachments)->toHaveCount(0);
});
