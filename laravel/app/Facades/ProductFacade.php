<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\Product[] published(array $fields = ['id', 'name', 'price'])
 * @see \App\Services\Product\ProductService
 */
class ProductFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'product';
    }
}

