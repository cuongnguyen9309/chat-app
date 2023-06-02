<?php

namespace App\Events;

use App\Models\Group;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MessageReact implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public array $channels = [];
    public $reactions;

    public function __construct(public $message, public $message_type)
    {

        $reaction_relation = '';
        if ($message_type === 'user') {
            $reaction_relation = 'message_reaction_user';
            ($this->channels)[] = new PrivateChannel('chat.' . $message->receiver_id);
            ($this->channels)[] = new PrivateChannel('chat.' . $message->sender_id);
        } else {
            $reaction_relation = 'group_message_reaction_user';
            $group = Group::find($message->receiver_id);
            $users_id = $group->users->pluck('id');
            foreach ($users_id as $user_id) {
                ($this->channels)[] = new PrivateChannel('chat.' . $user_id);
            }
        }
        $this->reactions = DB::table('reactions')
            ->join($reaction_relation . ' as reaction_relation', 'reaction_relation.reaction_id', '=', 'reactions.id')
            ->join('users', 'users.id', 'reaction_relation.user_id')
            ->where('reaction_relation.message_id', $message->id)
            ->select('reactions.*', 'users.name as user_name', 'reaction_relation.user_id as user_id', 'reaction_relation.message_id as message_id', 'reaction_relation.id as relation_id')
            ->get();

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return $this->channels;
    }
}
