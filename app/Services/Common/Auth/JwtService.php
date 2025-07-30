<?php

namespace App\Services\Common\Auth;

use App\Libraries\Predis;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use App\Models\SystemUsersModel;

class JwtService
{
    private string $privateKey;
    private string $publicKey;
    private string $algorithm = 'RS256';
    private string $redisPrefix = 'jwt:';

    public function __construct()
    {
        $this->privateKey = file_get_contents(storage_path('oauth-private.key'));
        $this->publicKey = file_get_contents(storage_path('oauth-public.key'));

    }

    public function generateToken(SystemUsersModel $user, $clientId, $scopes, $expireSeconds): string
    {
        $payload = [
            'iss' => config('app.url'),
            'iat' => time(),
            'exp' => time() + $expireSeconds,
            'sub' => $user->id,
            'jti' => uniqid(), // JWT ID 防止重放攻击
            'tenant_id' => $user->tenant_id,
            'client_id' => $clientId,
            'scopes' => $scopes,
            'user_info' => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'avatar' => $user->avatar,
                'tenant_id' => $user->tenant_id
            ]
        ];

        return JWT::encode($payload, $this->privateKey, $this->algorithm);
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->publicKey, $this->algorithm));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            return ['error' => 'token_expired'];
        } catch (SignatureInvalidException $e) {
            return ['error' => 'token_invalid'];
        } catch (\Exception $e) {
            return ['error' => 'token_validation_failed'];
        }
    }

    public function storeToken(string $token, array $data, int $expire): void
    {
        Predis::getInstance()->setex($this->redisPrefix . $token, $expire, json_encode($data));
    }

    public function revokeToken(string $token): void
    {
        Predis::getInstance()->del($this->redisPrefix . $token);
    }

    public function isTokenValid(string $token): bool
    {
        return Predis::getInstance()->exists($this->redisPrefix . $token);
    }

    public function getTokenData(string $token): ?array
    {
        $data = Predis::getInstance()->get($this->redisPrefix . $token);
        return $data ? json_decode($data, true) : null;
    }
}