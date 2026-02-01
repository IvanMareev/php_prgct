<?php

namespace App\Models;

use Database\Factories\ProductImageFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ProductImage
 *
 * @property int $id
 * @property int|null $product_id
 * @property string|null $path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product|null $product
 * @method static ProductImageFactory factory($count = null, $state = [])
 * @method static Builder|ProductImage newModelQuery()
 * @method static Builder|ProductImage newQuery()
 * @method static Builder|ProductImage query()
 * @method static Builder|ProductImage whereCreatedAt($value)
 * @method static Builder|ProductImage whereId($value)
 * @method static Builder|ProductImage wherePath($value)
 * @method static Builder|ProductImage whereProductId($value)
 * @method static Builder|ProductImage whereUpdatedAt($value)
 * @property string|null $url
 * @method static Builder|ProductImage whereUrl($value)
 */
class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'url'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
