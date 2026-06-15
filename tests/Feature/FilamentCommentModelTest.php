<?php

use Parallax\FilamentComments\Models\FilamentComment;
use Parallax\FilamentComments\Tests\Fixtures\Post;
use Parallax\FilamentComments\Tests\Fixtures\User;

it('uses the configured comments table name', function (): void {
    config()->set('filament-comments.table_name', 'custom_comments');

    expect((new FilamentComment)->getTable())->toBe('custom_comments');
});

it('relates comments to their subject and user', function (): void {
    $user = User::create([
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
    ]);

    $record = Post::create([
        'title' => 'Testing relations',
    ]);

    $olderComment = FilamentComment::create([
        'user_id' => $user->id,
        'subject_type' => $record->getMorphClass(),
        'subject_id' => $record->id,
        'comment' => 'Older comment',
    ]);
    $olderComment->forceFill([
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ])->save();

    $newerComment = FilamentComment::create([
        'user_id' => $user->id,
        'subject_type' => $record->getMorphClass(),
        'subject_id' => $record->id,
        'comment' => 'Newer comment',
    ]);
    $newerComment->forceFill([
        'created_at' => now(),
        'updated_at' => now(),
    ])->save();

    FilamentComment::create([
        'user_id' => $user->id,
        'subject_type' => 'unrelated-subject',
        'subject_id' => $record->id,
        'comment' => 'Different subject type',
    ]);

    expect($record->filamentComments()->pluck('id')->all())->toBe([
        $newerComment->id,
        $olderComment->id,
    ]);

    expect($newerComment->user->is($user))->toBeTrue();
});

it('only prunes soft deleted comments older than the configured retention window', function (): void {
    config()->set('filament-comments.prune_after_days', 7);

    $user = User::create([
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
    ]);

    $record = Post::create([
        'title' => 'Testing pruning',
    ]);

    $oldDeletedComment = FilamentComment::create([
        'user_id' => $user->id,
        'subject_type' => $record->getMorphClass(),
        'subject_id' => $record->id,
        'comment' => 'Old deleted comment',
    ]);
    $oldDeletedComment->forceFill([
        'created_at' => now()->subDays(8),
        'updated_at' => now()->subDays(8),
    ])->save();
    $oldDeletedComment->delete();

    $recentDeletedComment = FilamentComment::create([
        'user_id' => $user->id,
        'subject_type' => $record->getMorphClass(),
        'subject_id' => $record->id,
        'comment' => 'Recent deleted comment',
    ]);
    $recentDeletedComment->delete();

    $activeComment = FilamentComment::create([
        'user_id' => $user->id,
        'subject_type' => $record->getMorphClass(),
        'subject_id' => $record->id,
        'comment' => 'Active comment',
    ]);
    $activeComment->forceFill([
        'created_at' => now()->subDays(30),
        'updated_at' => now()->subDays(30),
    ])->save();

    expect((new FilamentComment)->prunable()->pluck('id')->all())
        ->toContain($oldDeletedComment->id)
        ->not->toContain($recentDeletedComment->id)
        ->not->toContain($activeComment->id);
});
