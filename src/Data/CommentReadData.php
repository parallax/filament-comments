<?php

namespace Parallax\FilamentComments\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CommentReadData extends Data
{
    public function __construct(
        public ?int $id,
        public int $comment_id,
        public int $user_id,
        public Carbon $read_at,
        public ?Carbon $created_at = null,
        public ?Carbon $updated_at = null,
    ) {
    }
} 