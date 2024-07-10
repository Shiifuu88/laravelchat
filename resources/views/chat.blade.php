<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        @if ($currentChatRoomId)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <p>You are currently in a chat room. Click <a href="{{ route('chatrooms.show', $currentChatRoomId) }}" class="text-blue-600">here</a> to return to the chat room.</p>
                    <button type="button" onclick="leaveRoom()" class="mt-2 p-2 bg-red-500 text-white rounded-lg hover:bg-red-700">Leave Room</button>
                </div>
            </div>
        @else
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Available Chat Rooms</h3>
                    <ul class="space-y-2">
                        @foreach ($chatRooms as $chatRoom)
                            <li>
                                <a href="{{ route('chatrooms.show', $chatRoom->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $chatRoom->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <input type="hidden" id="chatRoomId" value="{{ $currentChatRoomId }}">
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function leaveRoom() {
        const chatRoomId = document.getElementById('chatRoomId').value;

        axios.post('/chatrooms/leave', {
            chat_room_id: chatRoomId,
        }, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        }).then(response => {
            window.location.href = '/';
        }).catch(error => {
            console.error('Error leaving room:', error);
        });
    }

    // Make functions available globally
    window.leaveRoom = leaveRoom;
});
</script>
