<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string|null $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $image_url
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $admin_of_groups
 * @property-read int|null $admin_of_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $created_groups
 * @property-read int|null $created_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $friends
 * @property-read int|null $friends_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GroupMessage> $group_messages_sent
 * @property-read int|null $group_messages_sent_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $isFriendOf
 * @property-read int|null $is_friend_of_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $joined_groups
 * @property-read int|null $joined_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages_received
 * @property-read int|null $messages_received_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages_sent
 * @property-read int|null $messages_sent_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GroupMessage> $unseen_group_messages
 * @property-read int|null $unseen_group_messages_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'add_friend_link'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendship', 'user_id', 'friend_id')->wherePivot('status', 'accepted')->withTimestamps();
    }

    public function isFriendOf(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendship', 'friend_id', 'user_id')->wherePivot('status', 'accepted')->withTimestamps();
    }

    public function inRequestFriends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendship', 'user_id', 'friend_id')->wherePivot('status', 'pending')->withTimestamps();
    }

    public function isRequestingToBeFriend(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendship', 'friend_id', 'user_id')->wherePivot('status', 'pending')->withTimestamps();
    }

    public function joined_groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'group_id')
            ->withTimestamps()
            ->wherePivot('status', 'accepted');
    }

    public function pending_groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'group_id')
            ->withTimestamps()
            ->wherePivot('status', 'pending');
    }

    public function admin_of_groups(): HasMany
    {
        return $this->hasMany(Group::class, 'admin_id', 'id');
    }

    public function created_groups(): HasMany
    {
        return $this->hasMany(Group::class, 'created_by', 'id');
    }

    public function messages_sent(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id', 'id');
    }

    public function messages_received(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id', 'id');
    }

    public function group_messages_sent(): HasMany
    {
        return $this->hasMany(GroupMessage::class, 'sender_id', 'id');
    }

    public function unseen_group_messages(): BelongsToMany
    {
        return $this->belongsToMany(GroupMessage::class, 'group_message_user_unseen', 'user_id', 'group_message_id')->withTimestamps();
    }

    public function message_reactions(): BelongsToMany
    {
        return $this->belongsToMany(Reaction::class, 'message_reaction_user', 'reaction_id', 'user_id');
    }

    public function group_message_reactions(): BelongsToMany
    {
        return $this->belongsToMany(Reaction::class, 'group_message_reaction_user', 'reaction_id', 'user_id');
    }

    public function reacted_messages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_reaction_user', 'message_id', 'user_id');
    }

    public function reacted_group_messages(): BelongsToMany
    {
        return $this->belongsToMany(GroupMessage::class, 'group_message_reaction_user', 'group_message_id', 'user_id');
    }

    public function toSearchableArray()
    {
        $array = $this->only('id', 'name', 'email');
        return $array;
    }
}
