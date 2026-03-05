<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $timeout = 5; // minutos

        if (session()->has('lastActivity') && now()->diffInMinutes(session('lastActivity')) > $timeout) {
            Auth::logout();
            return redirect()->route('login')->with('mensaje', 'Tu sesión ha expirado por inactividad.');
        }

        session(['lastActivity' => now()]);
        return $next($request);
    }
}
