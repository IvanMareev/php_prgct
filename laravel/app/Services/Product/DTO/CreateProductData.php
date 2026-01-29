<?php
namespace App\Services\Product\DTO;

use App\Enums\ProductStatus;

final class CreateProductData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly float $price,
        public readonly int $count,
        public readonly ?array $images,
        public readonly ProductStatus $status,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'count' => $this->count,
            'status' => $this->status->value,
        ];
    }

    public function images(): ?array
    {
        return $this->images;
    }
}
