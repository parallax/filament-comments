<?php

namespace Parallax\FilamentComments\Actions;

use Filament\Support\Enums\Width;
use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
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
            ->badge(function(?Model $record) {
                return $record?->filamentComments()->count();
            })
            ->slideOver()
            ->modalContentFooter(fn (): View => view('filament-comments::component'))
            ->modalHeading(__('filament-comments::filament-comments.modal.heading'))
            ->modalWidth(Width::Medium)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->visible(fn (): bool => auth()->user()->can('viewAny', config('filament-comments.comment_model')));
    }
}
