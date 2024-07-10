<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\ChatRoom;

class UserLeftRoom implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $chatRoom;

    public function __construct(User $user, ChatRoom $chatRoom)
    {
        $this->user = $user;
        $this->chatRoom = chatRoom;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chatroom.' . $this->chatRoom->id);
    }
}