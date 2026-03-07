<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $timeout = 5; // minutos

        // Si no hay usuario logueado
        if (!$request->session()->has('user_id')) {
            return redirect('/login')->with('mensaje', 'Debes iniciar sesión.');
        }

        // Revisar inactividad
        $lastActivity = $request->session()->get('lastActivity');
        if ($lastActivity && now()->diffInMinutes($lastActivity) > $timeout) {
            $request->session()->flush(); // eliminar sesión
            return redirect('/login')->with('mensaje', 'Tu sesión ha expirado por inactividad.');
        }

        // Actualizar última actividad
        $request->session()->put('lastActivity', now());

        return $next($request);
    }
}
