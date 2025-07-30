<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;

class JwtMiddleware
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json(['error' => 'token_not_provided'], 401);
        }

        $payload = $this->jwtService->validateToken($token);

        if (isset($payload['error'])) {
            return response()->json(['error' => $payload['error']], 401);
        }

        if (!$this->jwtService->isTokenValid($token)) {
            return response()->json(['error' => 'token_revoked'], 401);
        }

        // 将用户信息附加到请求
        $request->merge([
            'user_id' => $payload['sub'],
            'user_info' => $payload['user_info'],
            'client_id' => $payload['client_id']
        ]);

        return $next($request);
    }

    private function getTokenFromRequest(Request $request)
    {
        if ($request->bearerToken()) {
            return $request->bearerToken();
        }

        if ($request->has('access_token')) {
            return $request->input('access_token');
        }

        return null;
    }
}