<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserLeavesChatRoom
{
    public function handle($request, Closure $next)
    {
        $currentChatRoomId = session('current_chat_room_id');
        $requestedChatRoomId = $request->route('chatRoom') ? $request->route('chatRoom')->id : null;

        if (Auth::check() && $currentChatRoomId && $currentChatRoomId != $requestedChatRoomId) {
            // Update the session table
            $userSession = \App\Models\Session::where('user_id', Auth::id())->first();
            if ($userSession) {
                $userSession->chat_room_id = null;
                $userSession->chat_room_name = null;
                $userSession->save();
            }

            // Forget the session variables
            session()->forget(['current_chat_room_id', 'current_chat_room_name']);

            // Trigger UserLeftRoom event
            event(new \App\Events\UserLeftRoom(Auth::user(), $currentChatRoomId));
        }

        return $next($request);
    }
}
