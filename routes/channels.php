<?php

use App\Models\User;
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

Broadcast::channel('chat.{id}', function (User $user, $id) {
    // Verifica si el usuario está autorizado para escuchar este canal
    // Aquí debes asegurar que el usuario esté asociado con la conversación con el ID proporcionado
    return $user->conversaciones()->where('id', $id)->exists();
});



Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
