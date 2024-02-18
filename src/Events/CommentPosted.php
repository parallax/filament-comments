<?php

namespace Parallax\FilamentComments\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Parallax\FilamentComments\Models\FilamentComment;

class CommentPosted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(FilamentComment $comment)
    {
    }
}
