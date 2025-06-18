<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GudangOpnameMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isOpname = ! $request->user()->lokasi->flag;

        if ($isOpname) {
            return response()->json([
                'status' => false,
                'message' => "Gudang yang Anda tempati masih dalam proses opname!",
            ], 409);
        }

        return $next($request);
    }
}
