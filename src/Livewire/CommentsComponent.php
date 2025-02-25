<?php

namespace Parallax\FilamentComments\Livewire;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
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
        if (!auth()->user()->can('create', config('filament-comments.comment_model'))) {
            return $form;
        }

        $schema = [];

        if (config('filament-comments.notify_users')) {
            $users = config('filament-comments.authenticatable')::query()
                ->where('id', '!=', auth()->id())
                ->pluck(config('filament-comments.user_name_attribute'), 'id');
            $schema[] = Forms\Components\Select::make('users_to_notify')
                ->hiddenLabel()
                ->placeholder(__('filament-comments::filament-comments.notify_users.placeholder'))
                ->options($users)
                ->multiple()
                ->searchable()
                ->preload();
        }

        if (config('filament-comments.editor') === 'markdown') {
            $schema[] = Forms\Components\MarkdownEditor::make('comment')
                ->hiddenLabel()
                ->required()
                ->placeholder(__('filament-comments::filament-comments.comments.placeholder'))
                ->toolbarButtons(config('filament-comments.toolbar_buttons'));
        } else {
            $schema[] = Forms\Components\RichEditor::make('comment')
                ->hiddenLabel()
                ->required()
                ->placeholder(__('filament-comments::filament-comments.comments.placeholder'))
                ->extraInputAttributes(['style' => 'min-height: 6rem'])
                ->toolbarButtons(config('filament-comments.toolbar_buttons'));
        }

        return $form
            ->schema($schema)
            ->statePath('data');
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
        ]);

        Notification::make()
            ->title(__('filament-comments::filament-comments.notifications.created'))
            ->success()
            ->send();

        if (config('filament-comments.notify_users')) {
            $title = __('filament-comments::filament-comments.notify_users.notification_title', [
                'user' => auth()->user()[config('filament-comments.user_name_attribute')]
            ]);
            $viewAction = Action::make('view')
                ->label(__('filament-comments::filament-comments.notify_users.notification_action'))
                ->url(Filament::getResourceUrl($this->record, 'view'))
                ->color('info')
                ->close();
            Notification::make()
                ->title($title)
                ->body($data['comment'])
                ->icon(config('filament-comments.icons.action'))
                ->iconColor('info')
                ->actions([
                    $viewAction,
                ])
                ->sendToDatabase(config('filament-comments.authenticatable')::find($data['users_to_notify']));
        }

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
        $comments = $this->record->filamentComments()->with(['user'])->latest()->get();

        return view('filament-comments::comments', ['comments' => $comments]);
    }
}
