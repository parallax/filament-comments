<?php

namespace Parallax\FilamentComments\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\View\View;
use Parallax\FilamentComments\Models\FilamentComment;

class CommentsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'comments';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->hiddenLabel()
            ->icon(config('filament-comments.icons.action'))
            ->color('gray')
            ->badge($this->record->filamentComments()->count())
            ->slideOver()
            ->modalContentFooter(fn (): View => view('filament-comments::component'))
            ->modalHeading(__('filament-comments::filament-comments.modal.heading'))
            ->modalWidth(MaxWidth::Medium)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->visible(fn (): bool => auth()->user()->can('viewAny', config('filament-comments.comment_model')));
    }
}
