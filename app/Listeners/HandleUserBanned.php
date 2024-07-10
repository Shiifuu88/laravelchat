<?php

namespace App\Listeners;

use App\Events\UserBanned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleUserBanned
{
    public function handle(UserBanned $event)
    {
        // Logik, um den Benutzer aus dem Raum zu werfen
    }
}