<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $rol
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $rol): Response
    {
        // Si no ha iniciado sesión, mandar al login
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Si el rol no coincide, redirigir a su respectivo dashboard con seguridad
        if ($user->rol !== $rol) {
            if ($user->rol === 'admin') {
                $redirectUrl = '/admin/dashboard';
            } elseif ($user->rol === 'profesor') {
                $redirectUrl = '/profesor/dashboard';
            } else {
                $redirectUrl = '/alumno/dashboard';
            }

            return redirect($redirectUrl)->with('error', 'Acceso no autorizado.');
        }

        return $next($request);
    }
}
