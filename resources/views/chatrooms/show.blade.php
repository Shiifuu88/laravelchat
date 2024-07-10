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
                            <strong>{{ $message->user->name }}</strong>
                        </div>
                        <div class="message-content">{{ $message->message }}</div>
                        <div class="message-date text-sm text-gray-500">
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
                        <li id="user-{{ $user->id }}" class="user-item" onclick="showBanModal({{ $user->id }}, '{{ $user->name }}')">{{ $user->name }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
    <div id="flash-alert-container"></div>
    <input type="hidden" id="chatRoomId" value="{{ $chatRoom->id }}">

    <!-- Ban Modal -->
    <div id="banModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeBanModal()">&times;</span>
            <h2>Ban User</h2>
            <form id="banUserForm">
                @csrf
                <input type="hidden" id="banUserId" name="user_id">
                <input type="hidden" id="banChatRoomId" name="chat_room_id" value="{{ $chatRoom->id }}">
                <label for="banDuration">Ban Duration (optional):</label>
                <input type="datetime-local" id="banDuration" name="banned_until">
                <button type="submit" class="bg-red-500 text-white rounded-lg p-2">Ban User</button>
            </form>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const banModal = document.getElementById('banModal');
    const banUserForm = document.getElementById('banUserForm');
    const banUserId = document.getElementById('banUserId');

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
        }, 3000);
    }

    function showBanModal(userId, userName) {
        banUserId.value = userId;
        document.getElementById('banModal').style.display = 'block';
    }

    function closeBanModal() {
        document.getElementById('banModal').style.display = 'none';
    }

    banUserForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(banUserForm);
        const chatRoomId = document.getElementById('chatRoomId').value;

        axios.post(`/chatrooms/${chatRoomId}/ban`, formData)
            .then(response => {
                showFlashAlert('User banned successfully', 'success');
                closeBanModal();
            })
            .catch(error => {
                console.error('Error banning user:', error);
                showFlashAlert('Failed to ban user', 'danger');
            });
    });

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
                        <strong>${e.user.name}</strong>
                    </div>
                    <div class="message-content">${e.message.message}</div>
                    <div class="message-date text-sm text-gray-500">
                        ${new Date(e.message.created_at).toLocaleString()}
                    </div>
                `;
                messagesDiv.appendChild(newMessageDiv);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            })
            .listen('UserJoinedRoom', (e) => {
                const userList = document.getElementById('user-list');
                const existingUserItem = document.getElementById(`user-${e.user.id}`);

                if (!existingUserItem) {
                    const newUserItem = document.createElement('li');
                    newUserItem.classList.add('user-item');
                    newUserItem.id = `user-${e.user.id}`;
                    newUserItem.innerHTML = `<div>${e.user.name}</div>`;
                    userList.appendChild(newUserItem);
                }

                if (e.user.id == {{ Auth::id() }}) {
                    showFlashAlert(`You joined Room: ${e.chatRoom.name}`, 'success');
                } else {
                    showFlashAlert(`${e.user.name} joined the room`, 'success');
                }
            })
            .listen('UserLeftRoom', (e) => {
                const userItem = document.getElementById(`user-${e.userId}`);
                if (userItem) {
                    userItem.remove();
                }

                if (e.user.id != {{ Auth::id() }}) {
                    showFlashAlert(`${e.user.name} left the room`, 'warning');
                } else {
                    showFlashAlert('You left the room', 'warning');
                }
            })
            .listen('UserBanned', (e) => {
                const bannedUntil = e.bannedUntil ? ` until ${new Date(e.bannedUntil).toLocaleString()}` : '';
                if (e.userId == {{ Auth::id() }}) {
                    showFlashAlert(`You have been banned from this room${bannedUntil}`, 'danger');
                    window.location.href = '{{ route("chatrooms.index") }}';
                } else {
                    const userItem = document.getElementById(`user-${e.userId}`);
                    if (userItem) {
                        userItem.remove();
                    }
                    showFlashAlert(`A user has been banned from the room${bannedUntil}`, 'warning');
                }
            });
    }

    window.sendMessage = sendMessage;
    window.switchRoom = switchRoom;
    window.leaveRoom = leaveRoom;
    window.showBanModal = showBanModal;
    window.closeBanModal = closeBanModal;

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
