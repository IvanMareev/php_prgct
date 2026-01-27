<?php
namespace App\Services\Product\DTO;

use App\Http\Requests\Product\StoreRequest;
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

    public static function fromRequest(StoreRequest $request): self
    {
        $images = $request->file('images');

        if ($images && !is_array($images)) {
            $images = [$images];
        }

        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: (float)$request->validated('price'),
            count: (int)$request->validated('count'),
            images: $images,
            status: ProductStatus::from($request->validated('status')),
        );
    }

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
