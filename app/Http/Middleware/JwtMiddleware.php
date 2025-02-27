<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                  'isLogin' => false,
                  'success' => false,
                  'message' => 'Token is Invalid',
                  // 'tes' => JWTAuth::parseToken()->authenticate()
                ],401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                  'isLogin' => false,
                  'success' => false,
                  'message' => 'Token is Expired'
                ],401);
            }else{
                return response()->json([
                  'isLogin' => false,
                  'success' => false,
                  'message' => 'Authorization Token not found',
                  // 'tes' => JWTAuth::parseToken()->authenticate()
                ],401);
            }
        }
        return $next($request);
        // return $next($request);
    }
}
