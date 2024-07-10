<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ban;
use Carbon\Carbon;

class CheckIfBanned
{
    public function handle(Request $request, Closure $next)
    {
        $chatRoom = $request->route('chatRoom');
        if ($chatRoom) {
            $chatRoomId = $chatRoom->id;
            $userId = Auth::id();

            $ban = Ban::where('user_id', $userId)
                ->where('chat_room_id', $chatRoomId)
                ->where(function ($query) {
                    $query->whereNull('banned_until')
                          ->orWhere('banned_until', '>', Carbon::now());
                })
                ->first();

            if ($ban) {
                return redirect()->route('chatrooms.index')->with('error', 'You are banned from this room.');
            }
        }

        return $next($request);
    }
}