<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

class MessageController extends Controller
{
    public function store(Request $request, ChatRoom $chatRoom)
    {
        $message = new Message();
        $message->user_id = Auth::id();
        $message->chat_room_id = $chatRoom->id;
        $message->message = $request->message;
        $message->save();

        event(new MessageSent(Auth::user(), $message));

        return response()->json(['message' => $message->message]);
    }
}
