<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 401,
                'message' => 'Token not provided.'
            ], 401);
        }

        try {
            $credentials = JWT::decode($token, env('JWT_LOCAL_SECRET'), ['HS256']);
        } catch (ExpiredException $e) {
            // $newToken=JWTAuth::refresh($token);
            // dd($newToken);
            return response()->json([
                'error' => 517,
                'message' => 'Provided token is expired.'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => 401,
                'message' => 'An error while decoding token.'
            ], 400);
        }

        $user = User::find($credentials->sub);
        $request->auth = $user;

        return $next($request);
    }
}
