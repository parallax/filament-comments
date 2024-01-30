<?php

namespace Parallax\FilamentComments\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Parallax\FilamentComments\Models\FilamentComment;

trait HasFilamentComments
{
    public function filamentComments(): HasMany
    {
        return $this
            ->hasMany(FilamentComment::class, 'subject_id')
            ->where('subject_type', static::class)
            ->latest();
    }
}
