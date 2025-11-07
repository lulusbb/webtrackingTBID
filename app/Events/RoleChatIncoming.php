<?php

namespace App\Events;

use App\Models\RoleMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RoleChatIncoming implements ShouldBroadcast
{
    public function __construct(public RoleMessage $msg) {}

    public function broadcastOn(): array
    {
        // target channel private by role penerima
        return [new PrivateChannel('roles.'.$this->msg->recipient_role)];
    }

    public function broadcastAs(): string
    {
        return 'chat.incoming';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->msg->id,
            'from_role' => $this->msg->sender_role,
            'body'      => $this->msg->body,
            'at'        => optional($this->msg->created_at)->toIso8601String(),
        ];
    }
}
