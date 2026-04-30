<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    // I-cast natin pareho sa string para sigurado
    return (string) $user->id === (string) $id;
});
