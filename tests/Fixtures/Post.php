<?php

namespace Parallax\FilamentComments\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Post extends Model
{
    use HasFilamentComments;

    protected $guarded = [];
}
