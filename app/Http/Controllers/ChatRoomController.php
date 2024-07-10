<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\Ban;
use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\UserJoinedRoom;
use App\Events\UserLeftRoom;
use App\Events\UserBanned;
use Carbon\Carbon;

class ChatRoomController extends Controller
{
    public function index()
    {
        $chatRooms = ChatRoom::all();
        return view('chatrooms.index', compact('chatRooms'));
    }

    public function show(ChatRoom $chatRoom)
    {
        $messages = Message::where('chat_room_id', $chatRoom->id)->with('user')->get();
        $usersInRoom = $this->getUsersInRoom($chatRoom->id);
        $otherChatRooms = ChatRoom::where('id', '!=', $chatRoom->id)->get();

        // Aktualisiere die Sitzung des aktuellen Benutzers
        $userSession = Session::firstOrCreate(
            ['user_id' => Auth::id()],
            ['chat_room_id' => $chatRoom->id, 'chat_room_name' => $chatRoom->name]
        );
        $userSession->chat_room_id = $chatRoom->id;
        $userSession->chat_room_name = $chatRoom->name;
        $userSession->save();

        return view('chatrooms.show', compact('chatRoom', 'messages', 'usersInRoom', 'otherChatRooms'));
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

    public function leaveRoom(Request $request)
    {
        $chatRoomId = $request->input('chat_room_id');
        $user = Auth::user();

        event(new UserLeftRoom($user, $chatRoomId));

        // Clear session
        session()->forget('chat_room_id');
        session()->forget('chat_room_name');

        Session::where('id', session()->getId())->update([
            'chat_room_id' => null,
            'chat_room_name' => null,
        ]);

        return redirect()->route('chatrooms.index');
    }

    public function banUser(Request $request, ChatRoom $chatRoom)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'banned_until' => 'nullable|date',
        ]);

        $ban = Ban::create([
            'user_id' => $request->user_id,
            'chat_room_id' => $chatRoom->id,
            'banned_until' => $request->banned_until ? Carbon::parse($request->banned_until) : null,
        ]);

        // Setze die Sitzung des gebannten Benutzers zurück
        $userSession = Session::where('user_id', $request->user_id)->first();
        if ($userSession) {
            $userSession->chat_room_id = null;
            $userSession->chat_room_name = null;
            $userSession->save();
        }

        // Lösche auch die aktuelle Sitzung des Benutzers
        session()->forget('chat_room_id');
        session()->forget('chat_room_name');

        // Broadcast UserBanned Event
        broadcast(new UserBanned($request->user_id, $chatRoom->id, $ban->banned_until))->toOthers();

        return response()->json(['message' => 'User banned successfully']);
    }

    private function getUsersInRoom($chatRoomId)
    {
        return Session::where('chat_room_id', $chatRoomId)
            ->with('user')
            ->get()
            ->map(function ($session) {
                return $session->user;
            });
    }
}
