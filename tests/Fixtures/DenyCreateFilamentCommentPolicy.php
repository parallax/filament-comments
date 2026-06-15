<?php

namespace Parallax\FilamentComments\Tests\Fixtures;

use Illuminate\Contracts\Auth\Authenticatable;
use Parallax\FilamentComments\Policies\FilamentCommentPolicy;

class DenyCreateFilamentCommentPolicy extends FilamentCommentPolicy
{
    public function create(Authenticatable $user): bool
    {
        return false;
    }
}
