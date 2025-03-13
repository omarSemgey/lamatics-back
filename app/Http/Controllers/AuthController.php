<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$accessToken = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $refreshToken = JWTAuth::customClaims([
            'ttl' => (int) config('jwt.refresh_ttl')
        ])
        ->fromUser($user);

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]
        ])
        ->cookie(
            'access_token', 
            $accessToken,
            (int) config('jwt.ttl'),
            '/', 
            null,
            config('app.env') === 'production',
            true, 
            false,
            'Lax'
        )
        ->cookie(
            'refresh_token', 
            $refreshToken,
            (int) config('jwt.refresh_ttl'),
            '/',
            null,
            config('app.env') === 'production',
            true,
            false,
            'Lax'
        );
    }

    public function me()
    {
        try {
            if (!$token = request()->cookie('access_token')) {
                return response()->json([
                    'error' => 'unauthenticated'
                ], 401);
            }

            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json([
                    'error' => 'user_not_found'
                ], 404)
                ->withoutCookie('access_token')
                ->withoutCookie('refresh_token');
            }

            return response()->json([
                'status' => 'success', 
                'user' => $user
            ]);    

        } catch (\Exception $e) {
            return response()
            ->json([
                    'error' => 'invalid_token'
                ], 401)
                ->withoutCookie('access_token')
                ->withoutCookie('refresh_token');
        }    
    }

    public function register(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 1,
                'password' => Hash::make($request->password),
            ]);

            $accessToken = Auth::guard('api')->login($user);
            $refreshToken = JWTAuth::customClaims([
                'ttl' => (int) config('jwt.refresh_ttl')
            ])
            ->fromUser($user);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorization' => [
                    'type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ])
            ->cookie(
                'access_token', 
                $accessToken,
                (int) config('jwt.ttl'),
                '/', 
                null,
                config('app.env') === 'production',
                true, 
                false,
                'Lax'
            )
            ->cookie(
                'refresh_token', 
                $refreshToken,
                (int) config('jwt.refresh_ttl'),
                '/',
                null,
                config('app.env') === 'production',
                true,
                false,
                'Lax'
            );
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $refreshToken = request()->cookie('refresh_token');
            $accessToken = request()->cookie('access_token');

            JWTAuth::invalidate($refreshToken);
            JWTAuth::invalidate($accessToken);

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ])
            ->withoutCookie('access_token')
            ->withoutCookie('refresh_token');

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $oldRefreshToken = request()->cookie('refresh_token');

            if (!$oldRefreshToken) {
                throw new JWTException('No refresh token');
            }

            $user = JWTAuth::setToken($oldRefreshToken)->authenticate();

            $newAccessToken = auth('api')->login($user);
            $newRefreshToken = JWTAuth::customClaims([
                'ttl' => (int) config('jwt.refresh_ttl')
            ])
            ->fromUser($user);

            JWTAuth::invalidate($oldRefreshToken);

            return response()->json([
                'status' => 'success',
                'authorization' => [
                    'type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ])
            ->cookie(
                'access_token', 
                $newAccessToken,
                (int) config('jwt.ttl'),
                '/', 
                null,
                config('app.env') === 'production',
                true, 
                false,
                'Lax'
            )
            ->cookie(
                'refresh_token', 
                $newRefreshToken,
                (int) config('jwt.refresh_ttl'),
                '/',
                null,
                config('app.env') === 'production',
                true,
                false,
                'Lax'
            );

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed'
            ], 401)
            ->withoutCookie('access_token')
            ->withoutCookie('refresh_token');
        }
    }
}