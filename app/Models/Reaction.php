<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reaction extends Model
{
    use HasFactory;

    protected $table = 'reactions';
    protected $guarded = [];

    public function message_users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_reaction_table', 'user_id', 'reaction_id');
    }

    public function group_message_users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_message_reaction_table', 'user_id', 'reaction_id');
    }

    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_reaction_table', 'message_id', 'reaction_id');
    }
    
    public function group_messages(): BelongsToMany
    {
        return $this->belongsToMany(GroupMessage::class, 'group_message_reaction_table', 'group_message_id', 'reaction_id');
    }
}
