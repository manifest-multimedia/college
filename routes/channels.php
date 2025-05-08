<?php

use App\Models\ChatSession;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chat.{sessionId}', function ($user, $sessionId) {
    // Verify that the user has access to this chat session
    return ChatSession::where('session_id', $sessionId)
        ->where('user_id', $user->id)
        ->where('status', '!=', 'deleted')
        ->exists();
});