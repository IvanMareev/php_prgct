<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Comment
 *
 * @property int $id
 * @property int|null $post_id
 * @property int|null $user_id
 * @property string|null $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Post|null $post
 *
 * @method static CommentFactory factory($count = null, $state = [])
 * @method static Builder|Comment newModelQuery()
 * @method static Builder|Comment newQuery()
 * @method static Builder|Comment query()
 * @method static Builder|Comment whereCreatedAt($value)
 * @method static Builder|Comment whereId($value)
 * @method static Builder|Comment wherePostId($value)
 * @method static Builder|Comment whereText($value)
 * @method static Builder|Comment whereUpdatedAt($value)
 * @method static Builder|Comment whereUserId($value)
 *
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'text', 'user_id'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
