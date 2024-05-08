<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {

            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException){

                return response()->json(['success' => false, "message" => 'Token invalid'],401);

            }else if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException){

                return response()->json(['success' => false, "message" => 'Token expired'],401);

            }else{

                return response()->json(['success' => false, "message" => 'Authorization Token not found'],401);
            }
        }
        return $next($request);
    }
}

