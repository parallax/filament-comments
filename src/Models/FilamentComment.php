<?php

namespace Parallax\FilamentComments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class FilamentComment extends Model
{
    use MassPrunable;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'parent_id',
        'subject_type',
        'subject_id',
        'comment',
    ];

    public function __construct(array $attributes = [])
    {
        $config = Config::get('filament-comments');

        if (isset($config['table_name'])) {
            $this->setTable($config['table_name']);
        }

        parent::__construct($attributes);
    }

    public function user(): BelongsTo
    {
        $authenticatable = config('filament-comments.authenticatable');

        return $this->belongsTo($authenticatable, 'user_id');
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

    public function replies(): HasMany
    {
        return $this->hasMany(config('filament-comments.comment_model'), 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(config('filament-comments.comment_model'), 'parent_id');
    }
}
