<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\UserIndexResource;
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
                return response()->json([
                    'status' => false,
                    'message' => 'Data kredensial yang diberikan tidak valid'
                ], 401);
            }

            $user = auth()->user();

            // Custom claims untuk kedua token
            $claims = ['role' => $user->role->nama_role];

            // Access Token (15 menit)
            $accessToken = JWTAuth::claims($claims)->fromUser($user);

            // Refresh Token (1 minggu)
            $refreshToken = auth()->setTTL(60 * 24 * 7)->fromUser($user);

            return response()->json([
                'status' => true,
                'message' => "Access Token and Refresh Token untuk user {$user->nama_user}",
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat token!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get authenticated user
    public function getUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => "Data user {$user->nama_user} berhasil diambil!",
                'data' => new UserIndexResource($user),
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token yang diberikan invalid',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = auth()->user();

            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => true,
                'message' => "{$user->nama_user} berhasil log out!",
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal melakukan logout!',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    public function refresh(Request $request)
    {
        try {
            $token = $request->input('refresh_token');

            if (empty($token)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error membutuhkan refresh token'
                ], 400);
            }

            // Set refresh token saat ini
            auth()->setToken($token);

            // Refresh token
            $newToken = auth()->refresh();
            $user = auth()->user();

            // Custom claims untuk kedua token
            $claims = ['role' => $user->role->nama_role];

            // Access Token (15 menit)
            $newToken = JWTAuth::claims($claims)->fromUser($user);

            return response()->json([
                'status' => true,
                'message' => 'Access Token Information',
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in_minutes' => config('jwt.ttl'),
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token yang diberikan invalid',
                'error' => $e->getMessage(),
            ], 401);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token yang diberikan expired',
                'error' => $e->getMessage()
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak dapat melakukan refresh token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
