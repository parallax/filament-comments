<?php

namespace Parallax\FilamentComments;

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Parallax\FilamentComments\Livewire\CommentsComponent;
use Parallax\FilamentComments\Models\FilamentComment;
use Parallax\FilamentComments\Policies\FilamentCommentPolicy;

class FilamentCommentsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-comments';

    public static string $viewNamespace = 'filament-comments';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('parallax/filament-comments');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageBooted(): void
    {
        Livewire::component('comments', CommentsComponent::class);

        Gate::policy(config('filament-comments.comment_model'), config('filament-comments.model_policy', FilamentCommentPolicy::class));
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament_comments_table',
            'add_index_to_subject',
        ];
    }
}
