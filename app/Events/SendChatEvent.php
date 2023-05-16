<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public string $channel;

    public function __construct(public $message)
    {
        if ($this->message['type'] === 'user') {
            $idArray = array($this->message['sender_id'], $this->message['receiver_id']);
            sort($idArray);
            $channel = "chat-user_{$idArray[0]}_{$idArray[1]}";
        } else {
            $channel = "chat-group_{$this->message['receiver_id']}";
        }
        $this->channel = $channel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel($this->channel),
        ];
    }


}
