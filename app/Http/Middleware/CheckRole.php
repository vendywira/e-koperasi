<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles  Allowed roles (admin, editor, etc.)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $userRole = $user->role ?? 'editor';

        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Access denied. Required role: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}
