<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendListUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $friends_id = [];
    public $friend = null;
    public array $channels = [];

    public function __construct(public $id, public string $event, $user = null)
    {
        $this->friend = $user;
        if ($user) {
            $this->channels[] = new PrivateChannel('chat.' . $user->id);
        }
        $user = User::find($id);
        $this->friends_id = $user->friends->pluck('id');
        $this->channels[] = new PrivateChannel('chat.' . $this->id);
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
