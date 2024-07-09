<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Session;
class EnsureSingleChatRoom
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $chatRoomId = $request->route('chatroom')->id;

        // Session-Daten aktualisieren
        session(['chat_room_id' => $chatRoomId, 'joined_at' => now()]);

        return $next($request);
    }
}
