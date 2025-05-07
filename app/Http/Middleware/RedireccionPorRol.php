<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedireccionPorRol
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar si entra a /admin o /admin/dashboard
        if ($request->is('admin') || $request->is('admin/dashboard')) {
            $user = auth()->user();

            if ($user?->hasRole('cliente')) {
                return redirect()->route('filament.admin.pages.cliente-dashboard');
            }

            if ($user?->hasRole('instructor')) {
                return redirect()->route('filament.admin.pages.instructor-dashboard');
            }
        }

        return $next($request);
    }
}