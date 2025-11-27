<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || ($roles && (! $user->role || ! in_array($user->role->name, $roles, true)))) {
            abort(403, 'Akses ditolak');
        }

        return $next($request);
    }
}
