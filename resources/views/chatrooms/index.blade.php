<x-app-layout>

    <div class="main-container">

        <!-- Main chat area -->
        <div class="chat-container">
            <div class="chat-header">
            <h2 class="text-2xl font-bold mb-5">Chat Rooms</h2>
            </div>
            <div class="chat-body">
            <ul>
                    @foreach ($chatRooms as $chatRoom)
                        <li class="mb-3">
                            <a href="{{ url('/chatrooms', $chatRoom->id) }}" class="text-blue-500">{{ $chatRoom->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
