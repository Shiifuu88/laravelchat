import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


window.addEventListener('beforeunload', function (e) {
    navigator.sendBeacon('/chatrooms/leave/' + '{{ session("chat_room_id") }}');
});

window.addEventListener('unload', function (e) {
    navigator.sendBeacon('/chatrooms/leave/' + '{{ session("chat_room_id") }}');
});
