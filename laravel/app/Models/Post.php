<?php

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Post
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $user_id
 * @property string|null $title
 * @property string|null $body
 * @property string|null $thumbnail
 * @property PostStatus|null $status
 * @property int|null $views
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read Collection<int, Comment> $comments
 * @property-read int|null $comments_count
 * @property-read User|null $user
 *
 * @method static PostFactory factory($count = null, $state = [])
 * @method static Builder|Post newModelQuery()
 * @method static Builder|Post newQuery()
 * @method static Builder|Post query()
 * @method static Builder|Post whereBody($value)
 * @method static Builder|Post whereCategoryId($value)
 * @method static Builder|Post whereCreatedAt($value)
 * @method static Builder|Post whereId($value)
 * @method static Builder|Post whereStatus($value)
 * @method static Builder|Post whereThumbnail($value)
 * @method static Builder|Post whereTitle($value)
 * @method static Builder|Post whereUpdatedAt($value)
 * @method static Builder|Post whereUserId($value)
 * @method static Builder|Post whereViews($value)
 *
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'body',
        'thumbnail',
        'status',
        'views',
        'user_id',
        'role'
    ];

    protected $casts = [
        'views' => 'int',
        'status' => PostStatus::class,
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
