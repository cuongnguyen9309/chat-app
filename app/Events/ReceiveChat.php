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

class ReceiveChat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public array $channels = [];

    public function __construct(public $message, public $receiver_type, public $sender_name, public $attachment, public $attachmentThumbnail)
    {
        if ($receiver_type === 'user') {
            ($this->channels)[] = new PrivateChannel('chat.' . $message->receiver_id);
        } else {
            $group = Group::find($message->receiver_id);
            $users_id = $group->users->pluck('id');
            foreach ($users_id as $user_id) {
                ($this->channels)[] = new PrivateChannel('chat.' . $user_id);
            }
        }
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
