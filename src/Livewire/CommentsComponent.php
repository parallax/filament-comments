<?php

namespace Parallax\FilamentComments\Livewire;

use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
        return $form
            ->schema([
                Forms\Components\RichEditor::make('comment')
                    ->hiddenLabel()
                    ->required()
                    ->placeholder(__('filament-comments::filament-comments.comments.placeholder'))
                    ->extraInputAttributes(['style' => 'min-height: 6rem'])
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $this->form->validate();

        $data = $this->form->getState();

        $this->record->filamentComments()->create([
            'subject_type' => $this->record->getMorphClass(),
            'comment' => $data['comment'],
            'user_id' => auth()->id(),
        ]);

        $this->form->fill();
    }

    public function delete(int $id): void
    {
        FilamentComment::find($id)->delete();
    }

    public function render(): View
    {
        $comments = $this->record->filamentComments()->latest()->get();

        return view('filament-comments::comments', ['comments' => $comments]);
    }
}
