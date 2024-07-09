<?php

namespace App\Listeners;

use App\Events\UserJoinedRoom;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

class NotifyUserJoinedRoom
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserJoinedRoom $event)
    {
        \Log::info('User joined room: ' . $event->chatRoomId . ', User: ' . $event->user->name);
    }
}
