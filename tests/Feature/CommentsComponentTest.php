<?php

use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Illuminate\Support\Facades\Gate;
use Parallax\FilamentComments\Livewire\CommentsComponent;
use Parallax\FilamentComments\Models\FilamentComment;
use Parallax\FilamentComments\Tests\Fixtures\DenyCreateFilamentCommentPolicy;
use Parallax\FilamentComments\Tests\Fixtures\Post;
use Parallax\FilamentComments\Tests\Fixtures\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;

function mountCommentsComponent(Post $record): CommentsComponent
{
    $component = app(CommentsComponent::class);
    $component->setId('comments-test');
    $component->setName('comments');
    $component->record = $record;
    $component->mount();

    return $component;
}

beforeEach(function (): void {
    $this->user = User::create([
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
    ]);

    $this->record = Post::create([
        'title' => 'Testing Filament comments',
    ]);

    actingAs($this->user);
});

it('creates a comment for the mounted record', function (): void {
    $component = mountCommentsComponent($this->record);
    $component->data['comment'] = '<p>This needs tests.</p>';
    $component->create();

    assertDatabaseHas('filament_comments', [
        'user_id' => $this->user->id,
        'subject_type' => $this->record->getMorphClass(),
        'subject_id' => $this->record->id,
        'comment' => '<p>This needs tests.</p>',
    ]);
});

it('does not create a comment when the configured policy denies creation', function (): void {
    Gate::policy(FilamentComment::class, DenyCreateFilamentCommentPolicy::class);

    $component = mountCommentsComponent($this->record);
    $component->data['comment'] = '<p>Blocked.</p>';
    $component->create();

    expect(FilamentComment::query()->where('comment', '<p>Blocked.</p>')->exists())->toBeFalse();
});

it('only deletes comments owned by the authenticated user', function (): void {
    $ownedComment = FilamentComment::create([
        'user_id' => $this->user->id,
        'subject_type' => $this->record->getMorphClass(),
        'subject_id' => $this->record->id,
        'comment' => '<p>Mine.</p>',
    ]);

    $otherUser = User::create([
        'name' => 'Nuno Maduro',
        'email' => 'nuno@example.com',
    ]);

    $otherComment = FilamentComment::create([
        'user_id' => $otherUser->id,
        'subject_type' => $this->record->getMorphClass(),
        'subject_id' => $this->record->id,
        'comment' => '<p>Someone else.</p>',
    ]);

    mountCommentsComponent($this->record)->delete($ownedComment->id);

    assertSoftDeleted('filament_comments', [
        'id' => $ownedComment->id,
    ]);

    mountCommentsComponent($this->record)->delete($otherComment->id);

    assertDatabaseHas('filament_comments', [
        'id' => $otherComment->id,
        'deleted_at' => null,
    ]);
});

it('uses the configured editor component', function (): void {
    $richEditor = mountCommentsComponent($this->record)
        ->form
        ->getComponents()[0];

    expect($richEditor)->toBeInstanceOf(RichEditor::class);

    config()->set('filament-comments.editor', 'markdown');

    $markdownEditor = mountCommentsComponent($this->record)
        ->form
        ->getComponents()[0];

    expect($markdownEditor)->toBeInstanceOf(MarkdownEditor::class);
});

it('renders the Filament action modal outlet required by rich editor actions', function (): void {
    expect(is_subclass_of(CommentsComponent::class, HasActions::class))->toBeTrue();

    expect(file_get_contents(__DIR__.'/../../resources/views/comments.blade.php'))
        ->toContain('<x-filament-actions::modals />');
});

it('can mount the rich editor link action', function (): void {
    $component = mountCommentsComponent($this->record);
    $editor = $component->form->getComponents()[0];

    expect($editor)->toBeInstanceOf(RichEditor::class);

    $component
        ->mountAction('link', [
            'editorSelection' => [
                'anchor' => 1,
                'head' => 1,
            ],
            'url' => null,
            'shouldOpenInNewTab' => false,
        ], [
            'schemaComponent' => $editor->getKey(),
        ]);

    expect($component->mountedActions[0]['name'])->toBe('link')
        ->and($component->getMountedAction()->getName())->toBe('link')
        ->and($component->mountedActionShouldOpenModal())->toBeTrue();
});
