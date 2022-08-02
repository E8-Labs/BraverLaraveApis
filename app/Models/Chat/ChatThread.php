<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Chat\ChatUser;
use App\Models\Auth\User;

class ChatThread extends Model
{
    use HasFactory;
    protected $table = "chat";
    protected $primaryKey = 'chatid';
    public $timestamps = false;
    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';


    public function getChatUsers(){
    	$chatUserIds = ChatUser::where('chatid', $this->chatid)->pluck('userid')->toArray();
        $users = User::whereIn('userid', $chatUserIds)->get();
        return $users;
    }
}
