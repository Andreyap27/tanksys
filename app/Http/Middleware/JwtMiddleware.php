<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = session('jwt_token');

        if (!$token) {
            return redirect()->route('login');
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                session()->forget('jwt_token');
                return redirect()->route('login');
            }

            auth()->guard('web')->setUser($user);

        } catch (TokenExpiredException $e) {
            session()->forget('jwt_token');
            return redirect()->route('login')->withErrors(['session' => 'Sesi Anda telah berakhir, silakan login kembali.']);
        } catch (TokenInvalidException $e) {
            session()->forget('jwt_token');
            return redirect()->route('login');
        } catch (JWTException $e) {
            session()->forget('jwt_token');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
