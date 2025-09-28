<?php

use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Livewire\Comments;
use Kirschbaum\Commentions\Tests\Database\Factories\PostFactory;
use Kirschbaum\Commentions\Tests\Database\Factories\UserFactory;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

it('can upload files with a comment', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    // Simulate Filament FileUpload behavior - files are stored as paths
    $file1 = 'commentions/attachments/document.pdf';
    $file2 = 'commentions/attachments/image.jpg';

    Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'This is a test comment with files')
        ->set('attachments', [$file1, $file2])
        ->call('save');

    $comment = $post->comments()->latest()->first();

    expect($comment->body)->toBe('This is a test comment with files');
    expect($comment->attachments)->toHaveCount(2);
    expect($comment->attachments->first()->file_path)->toBe('commentions/attachments/document.pdf');
    expect($comment->attachments->last()->file_path)->toBe('commentions/attachments/image.jpg');
});

it('validates file upload rules', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    // Test max files validation
    $files = collect(range(1, 6))->map(fn () => 'commentions/attachments/test.pdf');

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

    $file1 = 'commentions/attachments/document.pdf';
    $file2 = 'commentions/attachments/image.jpg';

    $component = Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment')
        ->set('attachments', [$file1, $file2]);

    expect($component->get('attachments'))->toHaveCount(2);

    $component->call('removeAttachment', 0);

    expect($component->get('attachments'))->toHaveCount(1);
    expect($component->get('attachments')[0])->toBe('commentions/attachments/image.jpg');
});

it('clears attachments when comment is cleared', function () {
    $user = UserFactory::new()->create();
    $post = PostFactory::new()->create();

    $this->actingAs($user);

    $file = 'commentions/attachments/document.pdf';

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

    $file = 'commentions/attachments/document.pdf';

    Livewire::test(Comments::class, ['record' => $post])
        ->set('commentBody', 'Test comment')
        ->set('attachments', [$file])
        ->call('save');

    $attachment = $post->comments()->latest()->first()->attachments()->first();

    expect($attachment->file_path)->toBe('commentions/attachments/document.pdf');
    expect($attachment->filename)->toBe('document.pdf');
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
