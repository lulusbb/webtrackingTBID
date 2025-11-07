<?php
// app/Events/NotificationPushed.php
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationPushed implements ShouldBroadcast {
    public function __construct(public $userId, public array $item) {}
    public function broadcastOn() { return new PrivateChannel('users.'.$this->userId); }
    public function broadcastAs() { return 'notif.pushed'; }
}
