<?php

use Parallax\FilamentComments\Actions\CommentsAction;
use Parallax\FilamentComments\Infolists\Components\CommentsEntry;
use Parallax\FilamentComments\Tables\Actions\CommentsAction as TableCommentsAction;
use Parallax\FilamentComments\Tests\Fixtures\Post;
use Parallax\FilamentComments\Tests\Fixtures\User;

use function Pest\Laravel\actingAs;

it('configures the page comments action', function (): void {
    actingAs(User::create([
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
    ]));

    $record = Post::create([
        'title' => 'Testing actions',
    ]);

    $record->filamentComments()->create([
        'user_id' => auth()->id(),
        'subject_type' => $record->getMorphClass(),
        'comment' => 'Existing comment',
    ]);

    $action = CommentsAction::make()->record($record);

    expect($action->getName())->toBe('comments')
        ->and($action->getIcon())->toBe(config('filament-comments.icons.action'))
        ->and($action->isModalSlideOver())->toBeTrue()
        ->and($action->isVisible())->toBeTrue();
});

it('configures the table comments action', function (): void {
    actingAs(User::create([
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
    ]));

    $action = TableCommentsAction::make();

    expect($action->getName())->toBe('comments')
        ->and($action->getLabel())->toBe(__('filament-comments::filament-comments.comments'))
        ->and($action->getIcon())->toBe(config('filament-comments.icons.action'))
        ->and($action->isModalSlideOver())->toBeTrue()
        ->and($action->isVisible())->toBeTrue();
});

it('configures the infolist comments entry visibility', function (): void {
    actingAs(User::create([
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
    ]));

    expect(CommentsEntry::make('filament_comments')->isVisible())->toBeTrue();
});
