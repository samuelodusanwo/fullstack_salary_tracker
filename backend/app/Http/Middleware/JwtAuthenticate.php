<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        try {
            // Attempt to parse and authenticate the token
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->errorResponse(
                    "Unauthorized",
                    401,
                    [
                        'token' => ["Token is invalid or expired"]
                    ]
                );
            }
            
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                401,
                [
                    'token' => [$e->getMessage()]
                ]
            );
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                401,
                [
                    'token' => [$e->getMessage()]
                ]
            );
        } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                401,
                [
                    'token' => [$e->getMessage()]
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                401,
                [
                    'token' => [$e->getMessage()]
                ]
            );
        }
        
        return $next($request);
    }
    private function errorResponse($message, $statusCode, $errors = null)
    {
        return response()->json([
            'status' => false,
            'status_code' => $statusCode,
            'message' => $message,
            'data' => null,
            'errors' => $errors
        ], $statusCode);
    }
}
