<?php

namespace App\Services;

use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\PayloadFactory;
use Tymon\JWTAuth\JWTManager;
use App\Models\User;

class JwtService
{
    protected $jwtAuth;
    protected $payloadFactory;
    protected $jwtManager;

    public function __construct(
        JWTAuth $jwtAuth,
        PayloadFactory $payloadFactory,
        JWTManager $jwtManager
    ) {
        $this->jwtAuth = $jwtAuth;
        $this->payloadFactory = $payloadFactory;
        $this->jwtManager = $jwtManager;
    }

    // 为用户生成 JWT
    public function generateToken(User $user): string
    {
        $customClaims = $user->getJWTCustomClaims();
        $payload = $this->payloadFactory->make(array_merge(
            ['sub' => $user->getJWTIdentifier()],
            $customClaims
        ));

        return $this->jwtManager->encode($payload)->get();
    }

    // 验证 JWT
    public function validateToken(string $token): bool
    {
        try {
            $this->jwtAuth->setToken($token)->checkOrFail();
            return true;
        } catch (JWTException $e) {
            return false;
        }
    }

    // 从 JWT 获取用户
    public function getUserFromToken(string $token): ?User
    {
        try {
            $payload = $this->jwtAuth->setToken($token)->getPayload();
            return User::find($payload['sub']);
        } catch (JWTException $e) {
            return null;
        }
    }

    // 刷新 JWT
    public function refreshToken(string $token): string
    {
        try {
            $this->jwtAuth->setToken($token);
            $newToken = $this->jwtAuth->refresh();
            return $newToken;
        } catch (JWTException $e) {
            throw $e;
        }
    }
}