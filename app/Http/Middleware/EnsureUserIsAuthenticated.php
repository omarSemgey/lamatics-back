<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsureUserIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = $request->cookie('access_token');

            $user = JWTAuth::setToken($token)->authenticate();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'invalid-token',
            ], 401);
        }

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}
