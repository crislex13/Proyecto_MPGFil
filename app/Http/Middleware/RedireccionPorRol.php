<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedireccionPorRol
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $path = $request->path();

        if (!$user) {
            return $next($request);
        }

        // ✅ NO redirigir si ya está navegando dentro del panel
        if (
            ($user->hasAnyRole(['admin', 'supervisor', 'recepcionista']) && $path !== 'admin') ||
            ($user->hasRole(['cliente', 'instructor']) && $path === 'admin/dashboard-multiples') ||
            ($user->hasRole('cliente') && !$user->hasRole('instructor') && $path === 'admin/cliente-dashboard') ||
            ($user->hasRole('instructor') && !$user->hasRole('cliente') && $path === 'admin/instructor-dashboard')
        ) {
            return $next($request);
        }

        // ✅ Permitir avanzar si están en /admin
        if (
            $path === 'admin' &&
            $user->hasAnyRole(['admin', 'supervisor', 'recepcionista'])
        ) {
            return $next($request);
        }

        // 🚦 Redirigir desde la raíz /admin al dashboard correspondiente
        if ($path === 'admin') {
            if ($user->hasRole('cliente') && $user->hasRole('instructor')) {
                return redirect('/admin/dashboard-multiples');
            }

            if ($user->hasRole('cliente')) {
                return redirect('/admin/cliente-dashboard');
            }

            if ($user->hasRole('instructor')) {
                return redirect('/admin/instructor-dashboard');
            }
        }

        return $next($request);
    }
}