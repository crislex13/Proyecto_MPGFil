<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class RegistrarConexionUsuario
{
    public function handle(Login $event)
    {
        $user = $event->user;

        $user->update([
            'last_login_at' => now(),
        ]);
    }
}