<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ChatRoom;

class SetChatRoomSession
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('chat_room_id')) {
            $chatRoomId = $request->input('chat_room_id');
            $chatRoom = ChatRoom::find($chatRoomId);

            if ($chatRoom) {
                Session::put('chat_room_id', $chatRoomId);
                Session::put('chat_room_name', $chatRoom->name);
            }
        }

        return $next($request);
    }
}
