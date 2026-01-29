<?php

namespace App\Providers;

use App\Http\Resources\Product\ProductResource;
use App\Repositories\EloquentPostRepository;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\Product\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;
use App\Services\UploadFiles\FileUploadService;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\EloquentUserRepository;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FileUploadService::class, function ($app) {
            return new FileUploadService();
        });
        $this->app->bind(
            PostRepositoryInterface::class,
            EloquentPostRepository::class
        );
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
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
