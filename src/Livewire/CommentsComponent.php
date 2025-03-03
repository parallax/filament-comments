<?php

namespace Parallax\FilamentComments\Livewire;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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

        $comment = $this->record->filamentComments()->create([
            'subject_type' => $this->record->getMorphClass(),
            'comment' => $data['comment'],
            'user_id' => auth()->id(),
            'parent_id' => $this->replyToId,
        ]);

        $this->replyToId = null;

        // Notify other users about the new comment
        $this->notifyOtherUsers($comment);

        Notification::make()
            ->title(__('filament-comments::filament-comments.notifications.created'))
            ->success()
            ->send();

        $this->form->fill();
    }

    protected function notifyOtherUsers(FilamentComment $comment): void
    {
        // Get all users who have commented on this record except the current user
        $userIds = $this->record->filamentComments()
            ->where('user_id', '!=', auth()->id())
            ->pluck('user_id')
            ->unique();

        $authenticatableModel = config('filament-comments.authenticatable');
        $users = $authenticatableModel::whereIn('id', $userIds)->get();

        $urlCallback = config('filament-comments.record_url');

        foreach ($users as $user) {
            Notification::make()
                ->title(__('filament-comments::filament-comments.notifications.new_comment', [
                    'model' => class_basename($this->record),
                    'id' => $this->record->id
                ]))
                ->body($comment->user->{config('filament-comments.user_name_attribute')}.' commented: '.Str::limit($comment->comment,
                        100))
                ->when($urlCallback && is_callable($urlCallback),
                    fn(Notification $notification) => $notification->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label(__('filament-comments::filament-comments.notifications.view_record'))
                            ->url($urlCallback($this->record))
                            ->markAsRead()
                    ])
                )
                ->sendToDatabase($user);
        }
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
