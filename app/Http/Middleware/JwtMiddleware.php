<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            if ($e instanceof TokenExpiredException) {
                try {
                    $refreshedToken = JWTAuth::refresh(JWTAuth::getToken());
                    $user = JWTAuth::setToken($refreshedToken)->toUser();
                    
                    $response = $next($request);

                    return $response->withHeaders([
                        'Authorization' => "Bearer {$refreshedToken}",
                        'access_token' => $refreshedToken,
                        'token_type' => 'bearer',
                        'expires_in' => auth('api')->factory()->getTTL() * 60
                    ]);
                } catch (Exception $e) {
                    return response()->json([
                        'status' => 'false',
                        'message' => 'Token cannot be refreshed, please try again.',
                        'error' => $e->getMessage(),
                    ], 401);
                }
            }
            
            return response()->json([
                'status' => 'false',
                'message' => 'Token is invalid.',
                'error' => $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
