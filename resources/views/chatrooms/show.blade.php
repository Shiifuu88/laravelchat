<x-app-layout>
    <div class="main-container">
        <!-- Sidebar with other chat rooms -->
        <div class="sidebar">
            <h3>Other Chat Rooms</h3>
            <ul>
                @foreach ($otherChatRooms as $otherChatRoom)
                    <li>
                        <a href="javascript:void(0)" onclick="switchRoom({{ $otherChatRoom->id }}, '{{ $otherChatRoom->name }}')">
                            {{ $otherChatRoom->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <button class="leave-room-btn" onclick="leaveRoom()">Leave Room</button>
        </div>

        <!-- Main chat area -->
        <div class="chat-container">
            <div class="chat-header">
                <h3>{{ $chatRoom->name }}</h3>
            </div>
            <div id="messages" class="chat-body">
                @foreach ($messages as $message)
                    <div class="message-item {{ $message->user_id == Auth::id() ? 'own-message' : '' }}">
                        <div>
                            <strong>{{ $message->user->name }}:</strong> {{ $message->message }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $message->created_at->format('d.m.Y H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="chat-footer">
                <form id="sendMessageForm" class="flex w-full">
                    @csrf
                    <input type="hidden" name="chat_room_id" value="{{ $chatRoom->id }}">
                    <input id="messageInput" type="text" name="message" class="p-2 border rounded-lg flex-grow" placeholder="Type your message...">
                    <button type="submit" class="bg-blue-500 text-white rounded-lg hover:bg-blue-700 p-2">Send</button>
                </form>
            </div>
        </div>

        <!-- User list area -->
        <div class="user-list">
            <h3>Users in Room</h3>
            <ul id="user-list">
                <!-- Current user should always be at the top -->
                <li id="user-{{ Auth::id() }}" class="user-item">{{ Auth::user()->name }}</li>
                @foreach ($usersInRoom as $user)
                    @if ($user->id != Auth::id())
                        <li id="user-{{ $user->id }}" class="user-item">{{ $user->name }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
    <div id="flash-alert-container"></div>
    <input type="hidden" id="chatRoomId" value="{{ $chatRoom->id }}">
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const chatRoomId = document.getElementById('chatRoomId').value;

        axios.post(`/chatrooms/${chatRoomId}/messages`, {
            message: messageInput.value,
        }).then(response => {
            messageInput.value = '';
        }).catch(error => {
            console.error('Error sending message:', error);
        });
    }

    function switchRoom(newRoomId, newRoomName) {
        axios.post('{{ route("chatrooms.switch") }}', {
            chat_room_id: newRoomId,
            chat_room_name: newRoomName,
        }).then(response => {
            window.location.href = `/chatrooms/${newRoomId}`;
        }).catch(error => {
            console.error('Error switching room:', error);
        });
    }

    function leaveRoom() {
        axios.post('{{ route("chatrooms.leave") }}', {
            chat_room_id: '{{ session("chat_room_id") }}',
        }).then(response => {
            window.location.href = '{{ route("chatrooms.index") }}';
        }).catch(error => {
            console.error('Error leaving room:', error);
        });
    }

    function showFlashAlert(message, type = 'info') {
        const existingAlert = document.querySelector('.flash-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        const flashAlert = document.createElement('div');
        flashAlert.classList.add('flash-alert', `flash-alert-${type}`);
        flashAlert.innerText = message;

        document.getElementById('flash-alert-container').appendChild(flashAlert);

        setTimeout(() => {
            flashAlert.remove();
        }, 3000); // Remove after 3 seconds
    }

    if (typeof window.Echo !== 'undefined') {
        window.Echo.private(`chatroom.${document.getElementById('chatRoomId').value}`)
            .listen('MessageSent', (e) => {
                const messagesDiv = document.getElementById('messages');
                const newMessageDiv = document.createElement('div');
                newMessageDiv.classList.add('message-item');
                if (e.user.id == {{ Auth::id() }}) {
                    newMessageDiv.classList.add('own-message');
                }
                newMessageDiv.innerHTML = `
                    <div>
                        <strong>${e.user.name}:</strong> ${e.message.message}
                    </div>
                    <div class="text-sm text-gray-500">
                        ${new Date(e.message.created_at).toLocaleString()}
                    </div>
                `;
                messagesDiv.appendChild(newMessageDiv);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .listen('UserJoinedRoom', (e) => {
                const userList = document.getElementById('user-list');
                const existingUserItem = document.getElementById(`user-${e.user.id}`);

                // Check if the user is already in the list
                if (!existingUserItem) {
                    const newUserItem = document.createElement('li');
                    newUserItem.classList.add('user-item');
                    newUserItem.id = `user-${e.user.id}`;
                    newUserItem.innerHTML = `<div>${e.user.name}</div>`;
                    userList.appendChild(newUserItem);
                }

                // Show flash alert for user joining
                if (e.user.id == {{ Auth::id() }}) {
                    showFlashAlert(`You joined Room: ${e.chatRoom.name}`, 'success');
                } else {
                    showFlashAlert(`${e.user.name} joined the room`, 'success');
                }
            })
            .listen('UserLeftRoom', (e) => {
                const userItem = document.getElementById(`user-${e.user.id}`);
                if (userItem) {
                    userItem.remove();
                }

                // Show flash alert for user leaving
                if (e.user.id != {{ Auth::id() }}) {
                    showFlashAlert(`${e.user.name} left the room`, 'warning');
                }
            });
    }

    window.sendMessage = sendMessage;
    window.switchRoom = switchRoom;
    window.leaveRoom = leaveRoom;

    const messagesDiv = document.getElementById('messages');
    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    document.getElementById('sendMessageForm').addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage();
    });

    document.getElementById('messageInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });

});
</script>
