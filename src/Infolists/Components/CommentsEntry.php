<?php

namespace Parallax\FilamentComments\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Parallax\FilamentComments\Models\FilamentComment;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Parallax\FilamentComments\Models\Traits\HasCommentsConfig;

class CommentsEntry extends Entry
{
    use HasFilamentComments;
    use HasCommentsConfig;

    protected string $view = 'filament-comments::component';

    protected function setUp(): void
    {
        parent::setUp();

        $this->visible(fn (): bool => auth()->user()->can('viewAny', FilamentComment::class));
    }
}
