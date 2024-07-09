<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\UserJoinedRoom;
use App\Events\UserLeftRoom;

class ChatRoomController extends Controller
{
    public function index()
    {
        $chatRooms = ChatRoom::all();
        return view('chatrooms.index', compact('chatRooms'));
    }

    public function show(ChatRoom $chatRoom)
    {
        $messages = $chatRoom->messages()->with('user')->get();
        $otherChatRooms = ChatRoom::where('id', '!=', $chatRoom->id)->get();
        $usersInRoom = $chatRoom->users; // Assuming you have a users relationship in ChatRoom model

        // Update session
        $this->updateSession($chatRoom->id, $chatRoom->name);
        event(new UserJoinedRoom(Auth::user(), $chatRoom->id));
        
        return view('chatrooms.show', compact('chatRoom', 'messages', 'otherChatRooms', 'usersInRoom'));
    }

    public function switchRoom(Request $request)
    {
        $user = Auth::user();
        $newChatRoomId = $request->input('chat_room_id');
        $newChatRoomName = $request->input('chat_room_name');

        // Remove user from current room
        $currentChatRoomId = session('chat_room_id');
        if ($currentChatRoomId && $currentChatRoomId != $newChatRoomId) {
            event(new UserLeftRoom($user, $currentChatRoomId));
        }

        // Update session
        $this->updateSession($newChatRoomId, $newChatRoomName);

        // Add user to new room
        event(new UserJoinedRoom($user, $newChatRoomId));

        return response()->json(['status' => 'success']);
    }

    private function updateSession($chatRoomId, $chatRoomName)
    {
        $sessionId = session()->getId();

        Session::where('id', $sessionId)->updateOrCreate(
            ['id' => $sessionId],
            [
                'chat_room_id' => $chatRoomId,
                'chat_room_name' => $chatRoomName,
                'user_id' => Auth::id()
            ]
        );

        session(['chat_room_id' => $chatRoomId, 'chat_room_name' => $chatRoomName]);
    }

    public function leaveRoom(Request $request, $chatRoomId)
    {
        $user = Auth::user();
        $chatRoom = ChatRoom::find($chatRoomId);

        if ($chatRoom && $user) {
            $chatRoom->users()->detach($user->id);
            event(new UserLeftRoom($user, $chatRoomId));
        }

        return redirect()->route('chatrooms.index')->with('status', 'You have left the room');
    }
}
