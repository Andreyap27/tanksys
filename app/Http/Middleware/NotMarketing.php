<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotMarketing
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()?->role === 'Marketing') {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
