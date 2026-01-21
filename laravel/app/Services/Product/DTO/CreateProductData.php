<?php

namespace App\Services\Product\DTO;

use App\Enums\ProductStatus;
use PhpOption\Option;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class CreateProductData extends Data
{
    public string $name;
    #[MapInputName('desc')]
    public string|Option $description;
    public int $price;
    public int $count;
    public string|Option $images;
    #[MapInputName('state')]
    public ProductStatus $status;
}
