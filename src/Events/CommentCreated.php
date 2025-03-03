<?php

namespace Parallax\FilamentComments\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Parallax\FilamentComments\Models\FilamentComment;

class CommentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FilamentComment $comment,
        public Model $subject
    ) {
    }
}
