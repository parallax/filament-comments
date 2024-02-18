<?php

namespace Parallax\FilamentComments\Livewire;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Parallax\FilamentComments\Events\CommentDeleted;
use Parallax\FilamentComments\Events\CommentPosted;
use Parallax\FilamentComments\Models\FilamentComment;

class CommentsComponent extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Model $record;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        if (!auth()->user()->can('create', FilamentComment::class)) {
            return $form;
        }

        return $form
            ->schema([
                Forms\Components\RichEditor::make('comment')
                    ->hiddenLabel()
                    ->required()
                    ->placeholder(__('filament-comments::filament-comments.comments.placeholder'))
                    ->extraInputAttributes(['style' => 'min-height: 6rem'])
                    ->toolbarButtons(config('filament-comments.toolbar_buttons'))
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        if (!auth()->user()->can('create', FilamentComment::class)) {
            return;
        }

        $this->form->validate();

        $data = $this->form->getState();

        $comment = $this->record->filamentComments()->create([
            'subject_type' => $this->record->getMorphClass(),
            'comment' => $data['comment'],
            'user_id' => auth()->id(),
        ]);

        event(new CommentPosted($comment));

        Notification::make()
            ->title(__('filament-comments::filament-comments.notifications.created'))
            ->success()
            ->send();

        $this->form->fill();
    }

    public function delete(int $id): void
    {
        $comment = FilamentComment::find($id);

        if (!$comment) {
            return;
        }

        if (!auth()->user()->can('delete', $comment)) {
            return;
        }

        // Make a copy of the comment or relevant data before deletion
        $commentCopy = clone $comment;

        $comment->delete();

        event(new CommentDeleted($commentCopy));

        Notification::make()
            ->title(__('filament-comments::filament-comments.notifications.deleted'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        $comments = $this->record->filamentComments()->latest()->get();

        return view('filament-comments::comments', ['comments' => $comments]);
    }
}
