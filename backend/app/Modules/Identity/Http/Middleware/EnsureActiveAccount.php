<?php

namespace App\Modules\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->status === 'active' && $request->user()->person->status === 'active', 403, 'Esta cuenta está desactivada.');
        if ($request->user()->must_change_password && ! $request->is('api/v1/me', 'api/v1/me/password')) {
            abort(403, 'Debes actualizar tu contraseña antes de continuar.');
        }

        return $next($request);
    }
}
