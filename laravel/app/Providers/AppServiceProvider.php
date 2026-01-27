<?php

namespace App\Providers;

use App\Http\Resources\Product\ProductResource;
use App\Repositories\EloquentPostRepository;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\Product\EloquentProductRepository;
use App\Services\Product\ProductService;
use Illuminate\Support\ServiceProvider;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('product', ProductService::class);
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ProductResource::withoutWrapping();
    }
}
