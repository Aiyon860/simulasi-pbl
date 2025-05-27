<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $accessToken = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();

            // Custom claims untuk kedua token
            $claims = ['role' => $user->role->nama_role];

            // Access Token (15 menit)
            $accessToken = JWTAuth::claims($claims)->fromUser($user);

            // Refresh Token (1 minggu)
            $refreshToken = auth()->setTTL(60 * 24 * 7)->fromUser($user);

            return response()->json([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    // Get authenticated user
    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return response()->json(compact('user'));
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(Request $request)
    {
        try {
            // Ambil refresh_token dari body
            $token = $request->input('refresh_token');

            if (empty($token)) {
                return response()->json(['error' => 'Refresh token is required'], 400);
            }
            
            // Set token secara manual (karena tidak lewat header)
            JWTAuth::setToken($token);

            // Refresh token
            $newToken = JWTAuth::refresh();

            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in_minutes' => config('jwt.ttl') // default 60 menit
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid: ' . $e], 401);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token is expired'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }
    }
}
