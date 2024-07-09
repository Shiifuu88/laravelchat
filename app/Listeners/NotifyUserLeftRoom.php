<?php

namespace App\Listeners;

use App\Events\UserLeftRoom;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

class NotifyUserLeftRoom
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
    public function handle(UserLeftRoom $event)
    {
        \Log::info('User left room: ' . $event->chatRoomId . ', User: ' . $event->user->name);
    }
}
