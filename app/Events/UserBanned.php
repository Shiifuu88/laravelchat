<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserBanned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $chatRoomId;
    public $bannedUntil;

    public function __construct($userId, $chatRoomId, $bannedUntil)
    {
        $this->userId = $userId;
        $this->chatRoomId = $chatRoomId;
        $this->bannedUntil = $bannedUntil;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chatroom.' . $this->chatRoomId);
    }
}
