<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class ParseJWTCookies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $accessToken = $request->cookie('access_token');
            $refreshToken = $request->cookie('refresh_token');

            if (!$refreshToken) {
                return response()->json([
                    'error' => 'Unauthorized',
                ], 401);
            }

            if($accessToken){
                $user = JWTAuth::setToken($accessToken)->authenticate();
                auth()->setUser($user);
            } 

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'invalid-token',
            ], 401);
        }

        return $next($request);
    }
}
