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
use Parallax\FilamentComments\Models\FilamentComment;
use Parallax\FilamentComments\Models\FilamentCommentRead;

class CommentsComponent extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Model $record;

    public ?int $replyToId = null;

    public function mount(): void
    {
        $this->form->fill();
        $this->markCommentsAsRead();
    }

    protected function markCommentsAsRead(): void
    {
        $userId = auth()->id();
        $otherUsersComments = $this->record->filamentComments()
            ->where('user_id', '!=', $userId)
            ->get();

        if ($otherUsersComments->isNotEmpty()) {
            $otherUsersComments->each(function (FilamentComment $comment) use ($userId) {
                FilamentCommentRead::firstOrCreate([
                    'comment_id' => $comment->id,
                    'user_id' => $userId,
                ], [
                    'read_at' => now(),
                ]);
            });
        }
    }

    public function form(Form $form): Form
    {
        if (!auth()->user()->can('create', config('filament-comments.comment_model'))) {
            return $form;
        }

        if (config('filament-comments.editor') === 'markdown') {
            $editor = Forms\Components\MarkdownEditor::make('comment')
                ->hiddenLabel()
                ->required()
                ->placeholder(__('filament-comments::filament-comments.comments.placeholder'))
                ->toolbarButtons(config('filament-comments.toolbar_buttons'));
        } else {
            $editor = Forms\Components\RichEditor::make('comment')
                ->hiddenLabel()
                ->required()
                ->placeholder(__('filament-comments::filament-comments.comments.placeholder'))
                ->extraInputAttributes(['style' => 'min-height: 6rem'])
                ->toolbarButtons(config('filament-comments.toolbar_buttons'));
        }

        return $form
            ->schema([
                $editor,
            ])
            ->statePath('data');
    }

    public function startReply(int $commentId): void
    {
        $this->replyToId = $commentId;
        $this->form->fill();
    }

    public function create(): void
    {
        if (!auth()->user()->can('create', config('filament-comments.comment_model'))) {
            return;
        }

        $this->form->validate();

        $data = $this->form->getState();

        $this->record->filamentComments()->create([
            'subject_type' => $this->record->getMorphClass(),
            'comment' => $data['comment'],
            'user_id' => auth()->id(),
            'parent_id' => $this->replyToId,
        ]);

        $this->replyToId = null;

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

        $comment->delete();

        Notification::make()
            ->title(__('filament-comments::filament-comments.notifications.deleted'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        $comments = $this->record->filamentComments()
            ->with(['user', 'replies.user', 'reads'])
            ->whereNull('parent_id')
            ->latest()
            ->get();

        return view('filament-comments::comments', [
            'comments' => $comments,
            'userId' => auth()->id(),
        ]);
    }
}
