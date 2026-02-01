<?php

namespace App\Models;

use Database\Factories\ProductReviewFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\ProductReview
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $products_id
 * @property string|null $text
 * @property int|null $rating
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read User|null $user
 * @method static ProductReviewFactory factory($count = null, $state = [])
 * @method static Builder|ProductReview newModelQuery()
 * @method static Builder|ProductReview newQuery()
 * @method static Builder|ProductReview query()
 * @method static Builder|ProductReview whereCreatedAt($value)
 * @method static Builder|ProductReview whereId($value)
 * @method static Builder|ProductReview whereProductsId($value)
 * @method static Builder|ProductReview whereRating($value)
 * @method static Builder|ProductReview whereText($value)
 * @method static Builder|ProductReview whereUpdatedAt($value)
 * @method static Builder|ProductReview whereUserId($value)
 * @property int|null $product_id
 * @method static Builder|ProductReview whereProductId($value)
 */
class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'text',
        'rating',
    ];

    protected $casts = [
        'rating' => 'int'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }


}
