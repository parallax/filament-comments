# Filament Comments

[![Latest Version on Packagist](https://img.shields.io/packagist/v/parallax/filament-comments.svg?style=flat-square)](https://packagist.org/packages/parallax/filament-comments)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/parallax/filament-comments.svg?style=flat-square)](https://packagist.org/packages/parallax/filament-comments)

Add comments to your Filament Resources.

![logo](/assets/filament-comments.jpg)

## Installation

Install the package via composer:

```bash
composer require parallax/filament-comments
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filament-comments-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-comments-config"
```

You can publish the language file with:

```bash
php artisan vendor:publish --tag="filament-comments-lang"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-comments-views"
```

## Quickstart

### 1. Add model trait

First open the eloquent model you wish to add Filament Comments to.

```php
namespace App\Models;

use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Product extends Model
{
    use HasFilamentComments;
}
```

### 2. Add comments to Filament Resource

There are 3 ways of using this plugin in your Filament Resources:

#### 1. Page actions

Open the page where you want the comments action to appear, this will most likely be the `View` resource page.

Add the `CommentsAction` to the `getHeaderActions()` method.

```php
<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Parallax\FilamentComments\Actions\CommentsAction;

class ViewProduct extends ViewRecord
{
    protected function getHeaderActions(): array
    {
        return [
            CommentsAction::make(),
        ];
    }
}
```

You may customise the page action as you would any other action.

#### 2. Table actions

Open the resource where you want the comments table action to appear.

Add the `CommentsAction` to the `$table->actions()` method.

```php
<?php

namespace App\Filament\Resources;

use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class ProductResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->actions([
                CommentsAction::make(),
            ]);
    }
}
```

You may customise the table action as you would any other action.

#### 3. Infolist entry

It's also possible to use the comments component within your infolist.

Add the `CommentsEntry` to the `$infolist->schema()` method.

```php
<?php

namespace App\Filament\Resources;

use Parallax\FilamentComments\Infolists\Components\CommentsEntry;

class ProductResource extends Resource
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                CommentsEntry::make('filament_comments'),
            ]);
    }
}
```

Please note that this cannot be used in combination with a comments action on the same page.

## Authorisation

By default, all users can view & create comments as well as only delete their own comments.

If you would like to change this behaviour you can create your own eloquent model policy for the `Parallax\FilamentComments\Models\FilamentComment` model.

You should define the policy class in the `filament-comments` config file.

```php
return [
    'model_policy' => \Parallax\FilamentComments\Policies\FilamentCommentPolicy::class,
];
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Parallax](https://parall.ax)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
