<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $userRole = mb_strtolower(trim((string) $user->role));
        $allowedRoles = array_map(
            fn (string $role): string => mb_strtolower(trim($role)),
            $roles
        );

        if (empty($allowedRoles) || in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        abort(403, 'No tienes permisos para acceder a este modulo.');
    }
}
