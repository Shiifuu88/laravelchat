<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Session extends Model
{
    protected $table = 'sessions';

    protected $fillable = [
        'id', 'user_id', 'chat_room_id', 'chat_room_name', 'ip_address', 'user_agent', 'payload', 'last_activity'
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }
    
    public function currentRoom()
    {
        $session = DB::table('sessions')
            ->where('user_id', $this->id)
            ->whereNotNull('chat_room_id')
            ->first();

        return $session ? $session->chat_room_id : null;
    }
}