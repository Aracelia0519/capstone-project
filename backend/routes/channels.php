<?php

use Illuminate\Support\Facades\Broadcast;

// Allow the user to listen only to their own specific channel
Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});