<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * App\Models\GroupMessage
 *
 * @property int $id
 * @property string $content
 * @property int $sender_id
 * @property int $receiver_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Group|null $receiver
 * @property-read \App\Models\User|null $sender
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $unseen_users
 * @property-read int|null $unseen_users_count
 * @method static \Database\Factories\GroupMessageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage whereReceiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMessage withoutTrashed()
 * @mixin \Eloquent
 */
class GroupMessage extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = ['content', 'sender_id', 'receiver_id'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'receiver_id', 'id');
    }

    public function unseen_users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_message_user_unseen', 'group_message_id', 'user_id')->withTimestamps();
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachmentable');
    }

    public function toSearchableArray()
    {
        return $this->only('id', 'content');
    }
}
