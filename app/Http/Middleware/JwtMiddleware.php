<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class JwtMiddleware {
    public function handle($request, Closure $next) {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status' => false, 'message' => 'Token is invalid'], 401);
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status' => false, 'message' => 'Token is expired'], 401);
            } else {
                return response()->json(['status' => false, 'message' => 'Authorization token not found'], 401);
            }
        }
        return $next($request);
    }
}