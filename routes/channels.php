<?php

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

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('roles.{role}', function ($user, $role) {
    // izinkan siapa pun yang logged-in (punya role) untuk subscribe ke channel rolenya sendiri
    return strtolower($user->role ?? '') === strtolower($role);
});
