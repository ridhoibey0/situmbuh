<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** Pemakaian: ->middleware('role:kader,nakes'). Admin selalu lolos. */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user()?->roles;

        if (!$role || (!in_array($role->value, $roles, true) && !$role->isAdmin())) {
            abort(403);
        }

        return $next($request);
    }
}
