<?php

namespace Parallax\FilamentComments\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilamentComment extends Model
{
    use MassPrunable;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'comment',
    ];

    public function user(): BelongsTo
    {
        $authenticatable = app(Authenticatable::class);

        return $this->belongsTo($authenticatable::class, 'user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->morphTo();
    }

    public function prunable(): Builder
    {
        $days = config('filament-comments.prune_after_days');

        return static::onlyTrashed()->where('created_at', '<=', now()->subDays($days));
    }
}
