<?php

namespace App\Services\Product\DTO;

use App\Enums\ProductStatus;
use PhpOption\Option;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class UpdateProductData extends Data
{
    public string $name;
    public string|Option $description;
    public int $price;
    public int $count;
    public ProductStatus $status;
}
