<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Get the authenticated user.
            $user = auth()->user();

            // (optional) Attach the role to the token.
            $token = JWTAuth::claims(['role' => $user->role->nama_role])->fromUser($user);

            return DB::transaction(function () use ($user, $token) {
                // Update the user's token in the database.
                $user->update(['token_jwt' => $token]);

                return response()->json(compact('token'));
            });
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
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(compact('user'));
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        $user = auth()->user();

        return DB::transaction(function () use ($user) {
            // Remove the JWT token from the user record.
            $user->update(['token_jwt' => null]);

            return response()->json(['message' => 'Successfully logged out']);
        });
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            $user = auth()->user();

            return DB::transaction(function () use ($user, $token) {
                // Update the user's token in the database.
                $user->update(['token_jwt' => $token]);

                return response()->json(compact('token'));
            });
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }
    }
}
