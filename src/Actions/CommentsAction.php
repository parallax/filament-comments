<?php

namespace Parallax\FilamentComments\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\View\View;

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
            ->badge(fn() => $this->getUnreadCommentsCount())
            ->slideOver()
            ->modalContentFooter(fn (): View => view('filament-comments::component'))
            ->modalHeading(__('filament-comments::filament-comments.modal.heading'))
            ->modalWidth(MaxWidth::Medium)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->visible(fn (): bool => auth()->user()->can('viewAny', config('filament-comments.comment_model')));
    }

    protected function getUnreadCommentsCount(): int
    {
        return $this->record->filamentComments()
            ->whereDoesntHave('reads', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->where('user_id', '!=', auth()->id())
            ->count();
    }
}
