<?php 
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            // Retorna null para no redirigir y dejar que el manejo de errores se encargue de devolver una respuesta JSON adecuada.
            return null;
        }

        // Redirigir a la ruta de login si la solicitud no espera JSON.
        return route('login');
    }
}
