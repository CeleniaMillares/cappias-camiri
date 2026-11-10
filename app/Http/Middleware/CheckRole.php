<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $usuario = $request->user();

        if (!$usuario) {
            return redirect('/login');
        }

        $tienePermiso = $usuario->roles()->whereIn('nombre', $roles)->exists();

        if (!$tienePermiso) {
            // Si la petición espera JSON (API), devolvemos JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'No tienes permisos para acceder a este recurso.',
                    'roles_requeridos' => $roles,
                ], 403);
            }

            // Si es la web, mostramos error 403 o redirigimos al dashboard
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}