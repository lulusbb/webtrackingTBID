<?php

namespace App\Events;

use App\Models\RoleMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ChatIncoming implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public RoleMessage $message) {}

    public function broadcastOn(): array
    {
        // channel privat per role penerima
        return [new PrivateChannel('roles.'.$this->message->to_role)];
    }

    public function broadcastAs(): string
    {
        return 'chat.incoming';
    }

    public function broadcastWith(): array
    {
        return [
            'from_role' => $this->message->from_role,
            'to_role'   => $this->message->to_role,
            'body'      => $this->message->body,
            'id'        => $this->message->id,
        ];
    }
}
