<?php

namespace Parallax\FilamentComments\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Parallax\FilamentComments\Models\FilamentComment;

trait HasFilamentComments
{
    public function filamentComments(): HasMany
    {
        return $this
            ->hasMany(config('filament-comments.comment_model'), 'subject_id')
            ->where('subject_type', $this->getMorphClass())
            ->latest();
    }
}
