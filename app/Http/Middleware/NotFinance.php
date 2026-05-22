<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotFinance
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()?->role === 'Finance') {
            return redirect()->route('gaji.index');
        }

        return $next($request);
    }
}
