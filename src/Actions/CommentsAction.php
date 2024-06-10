<?php

namespace Parallax\FilamentComments\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
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

        $count = $this->record->filamentComments()->count();

        if (config('filament-comments.auto_open') && $count > 0) {
            FilamentView::registerRenderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => <<<JS
                    <script>
                        window.addEventListener("load", (event) => {
                            document.getElementById("toggle-comments").click()
                        });
                    </script>
                JS
            );
        }

        $this
            ->hiddenLabel()
            ->icon(config('filament-comments.icons.action'))
            ->color('gray')
            ->badge($count)
            ->slideOver()
            ->modalContentFooter(fn (): View => view('filament-comments::component'))
            ->modalHeading(__('filament-comments::filament-comments.modal.heading'))
            ->modalWidth(MaxWidth::Medium)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->extraAttributes(['id' => 'toggle-comments'])
            ->visible(fn (): bool => auth()->user()->can('viewAny', config('filament-comments.comment_model')));
    }
}
