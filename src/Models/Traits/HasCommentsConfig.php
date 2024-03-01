<?php

namespace Parallax\FilamentComments\Models\Traits;

use Illuminate\Support\Facades\Config;

trait HasCommentsConfig
{
    protected bool $showForm = true;
    public array $config;

    public function withoutForm()
    {
        $this->showForm = false;
        
        return $this;
    }

    public function getConfig()
    {
        $config = Config::get('filament-comments');

        $config['show_form'] = $this->showForm !== false;
        $config['show_form'] = false;

        return $config;
    }
}
