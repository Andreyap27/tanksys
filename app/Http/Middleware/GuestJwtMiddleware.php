<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class GuestJwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = session('jwt_token');

        if ($token) {
            try {
                $user = JWTAuth::setToken($token)->authenticate();
                if ($user) {
                    return redirect()->route('dashboard');
                }
            } catch (\Exception $e) {
                session()->forget('jwt_token');
            }
        }

        return $next($request);
    }
}
