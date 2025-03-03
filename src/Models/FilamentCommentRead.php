<?php

namespace Parallax\FilamentComments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parallax\FilamentComments\Data\CommentReadData;
use Spatie\LaravelData\WithData;

class FilamentCommentRead extends Model
{
    use WithData;

    protected string $dataClass = CommentReadData::class;

    protected $table = 'filament_comment_reads';

    protected $fillable = [
        'comment_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(FilamentComment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
