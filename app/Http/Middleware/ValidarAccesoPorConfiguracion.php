<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Configuracion;

class ValidarAccesoPorConfiguracion
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $config = Configuracion::first(); // Asumimos un solo registro
            $rol = $user->getRoleNames()->first();

            if (
                ($rol === 'cliente' && !$config->clientes_pueden_acceder) ||
                ($rol === 'instructor' && !$config->instructores_pueden_acceder)
            ) {
                auth()->logout();
                return redirect()->route('login')->withErrors(['access_denied' => 'Acceso denegado por el administrador.']);
            }
        }

        return $next($request);
    }
}
