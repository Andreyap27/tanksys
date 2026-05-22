<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FinanceOnly
{
    public function handle(Request $request, Closure $next)
    {
        $role = auth()->user()?->role;

        if (!in_array($role, ['Finance', 'Super Admin'])) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
