<?php
namespace App\Services\Product\DTO;
use App\Http\Requests\Product\UpdateProductRequest;

final class UpdateProductData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?float  $price,
        public readonly ?string $description,
        public readonly ?array  $images,
    )
    {
    }

    public static function fromRequest(UpdateProductRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            price: $request->validated('price'),
            description: $request->validated('description'),
            images: $request->file('images'),
        );
    }

    /** Только данные для update() */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
        ], fn($v) => $v !== null);
    }

    /** Отдельно файлы */
    public function images(): ?array
    {
        return $this->images;
    }
}
