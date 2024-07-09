<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('chatroom.{chatRoomId}', function ($user, $chatRoomId) {
    return Auth::check();
});