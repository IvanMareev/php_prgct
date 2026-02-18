<?php

namespace App\Providers;

use App\Adapters\Interfaces\TelegramInterface;
use App\Adapters\SendNotifyTelegramAdapter;
use App\Http\Resources\Product\ProductResource;
use App\Repositories\EloquentPostRepository;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\Product\CachedProductRepository;
use App\Repositories\Product\EloquentProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\User\EloquentUserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Telegram\Formatter\TelegramMessageFormatter;
use App\Services\Telegram\Formatter\TelegramMessageFormatterInterface;
use App\Services\Telegram\UrlGenerator\TelegramUrlGenerator;
use App\Services\Telegram\UrlGenerator\TelegramUrlGeneratorInterface;
use App\Services\UploadFiles\FileUploadService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FileUploadService::class, function ($app) {
            return new FileUploadService;
        });

        $this->app->singleton(SendNotifyTelegramAdapter::class);

        $this->app->bind(
            PostRepositoryInterface::class,
            EloquentPostRepository::class
        );

        if ($this->app->environment('testing')) {
            $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        } else {
            $this->app->bind(ProductRepositoryInterface::class, function ($app) {
                return new CachedProductRepository(
                    $app->make(EloquentProductRepository::class)
                );
            });
        }

        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
        $this->app->bind(
            TelegramInterface::class,
            SendNotifyTelegramAdapter::class
        );

        $this->app->bind(
            TelegramUrlGeneratorInterface::class,
            fn () => new TelegramUrlGenerator(
                config('telegram.base_url', 'https://api.telegram.org')
            ));

        $this->app->bind(
            TelegramMessageFormatterInterface::class,
            TelegramMessageFormatter::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ProductResource::withoutWrapping();
    }
}
